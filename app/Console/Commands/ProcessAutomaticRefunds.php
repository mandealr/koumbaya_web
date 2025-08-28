<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RefundService;
use Illuminate\Support\Facades\Log;

class ProcessAutomaticRefunds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'refunds:process-automatic 
                            {--dry-run : Run in dry-run mode without making changes}
                            {--lottery-id= : Process refunds for specific lottery ID}
                            {--force : Force processing even if already processed}';

    /**
     * The console command description.
     */
    protected $description = 'Process automatic refunds for expired lotteries with insufficient participants';

    protected $refundService;

    /**
     * Create a new command instance.
     */
    public function __construct(RefundService $refundService)
    {
        parent::__construct();
        $this->refundService = $refundService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting automatic refunds processing...');

        $dryRun = $this->option('dry-run');
        $lotteryId = $this->option('lottery-id');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('⚠️  Running in DRY-RUN mode - no changes will be made');
        }

        try {
            if ($lotteryId) {
                $this->processSingleLottery($lotteryId, $dryRun, $force);
            } else {
                $this->processAllEligibleLotteries($dryRun);
            }

            $this->info('✅ Automatic refunds processing completed successfully');

            // Afficher les statistiques
            $this->displayRefundStats();
        } catch (\Exception $e) {
            $this->error('Error processing automatic refunds: ' . $e->getMessage());
            Log::error('ProcessAutomaticRefunds command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Traiter une tombola spécifique
     */
    protected function processSingleLottery(int $lotteryId, bool $dryRun, bool $force)
    {
        $lottery = \App\Models\Lottery::with('product')->find($lotteryId);

        if (!$lottery) {
            $this->error("Lottery with ID {$lotteryId} not found");
            return;
        }

        $this->info("🎯 Processing lottery: {$lottery->lottery_number} ({$lottery->product->title})");

        // Vérifier les conditions
        $minParticipants = $lottery->product->min_participants ?? 10;
        $hasInsufficientParticipants = $lottery->sold_tickets < $minParticipants;
        $isExpired = $lottery->end_date <= now();
        $isCancelled = $lottery->status === 'cancelled';

        $this->line("   📊 Participants: {$lottery->sold_tickets}/{$lottery->max_tickets}");
        $this->line("   📅 End date: {$lottery->end_date->format('Y-m-d H:i')}");
        $this->line("   📈 Status: {$lottery->status}");
        $this->line("   🎯 Min participants: {$minParticipants}");

        if (!$hasInsufficientParticipants && !$isCancelled) {
            $this->info("✨ Lottery doesn't need refunds (sufficient participants)");
            return;
        }

        if (!$isExpired && !$isCancelled && !$force) {
            $this->warn("⏰ Lottery not yet expired, use --force to process anyway");
            return;
        }

        // Vérifier si déjà traité
        $existingRefunds = \App\Models\Refund::where('lottery_id', $lotteryId)->count();
        if ($existingRefunds > 0 && !$force) {
            $this->warn("⚠️  Refunds already exist for this lottery ({$existingRefunds} refunds), use --force to reprocess");
            return;
        }

        $reason = $isCancelled ? 'lottery_cancelled' : 'insufficient_participants';

        if ($dryRun) {
            $this->info("🔍 [DRY RUN] Would process refunds for reason: {$reason}");
            return;
        }

        $result = $this->refundService->processAutomaticRefunds($lottery, $reason);

        if ($result['success']) {
            $this->info("✅ Processed {$result['participant_count']} refunds totaling {$result['total_refunded']} FCFA");
        } else {
            $this->error("Failed to process refunds: " . $result['error']);
        }
    }

    /**
     * Traiter toutes les tombolas éligibles
     */
    protected function processAllEligibleLotteries(bool $dryRun)
    {
        $this->info('🔍 Checking for lotteries requiring automatic refunds...');

        if ($dryRun) {
            $this->checkEligibleLotteries();
            return;
        }

        $results = $this->refundService->checkAndProcessRefunds();

        $totalProcessed = 0;
        $totalRefunded = 0;

        // Traiter les tombolas avec participants insuffisants
        foreach ($results['insufficient_participants'] as $lotteryResult) {
            $lottery = $lotteryResult['lottery'];
            $result = $lotteryResult['result'];

            if ($result['success']) {
                $this->info("✅ {$lottery->lottery_number}: {$result['participant_count']} refunds, {$result['total_refunded']} FCFA");
                $totalProcessed += $result['participant_count'];
                $totalRefunded += $result['total_refunded'];
            } else {
                $this->error("{$lottery->lottery_number}: " . $result['error']);
            }
        }

        // Traiter les tombolas annulées
        foreach ($results['cancelled_lotteries'] as $lotteryResult) {
            $lottery = $lotteryResult['lottery'];
            $result = $lotteryResult['result'];

            if ($result['success']) {
                $this->info("✅ {$lottery->lottery_number} (cancelled): {$result['participant_count']} refunds, {$result['total_refunded']} FCFA");
                $totalProcessed += $result['participant_count'];
                $totalRefunded += $result['total_refunded'];
            } else {
                $this->error("{$lottery->lottery_number} (cancelled): " . $result['error']);
            }
        }

        $this->info("📈 Total: {$totalProcessed} refunds processed, {$totalRefunded} FCFA refunded");
    }

    /**
     * Vérifier les tombolas éligibles (mode dry-run)
     */
    protected function checkEligibleLotteries()
    {
        // Tombolas expirées avec participants insuffisants
        $expiredLotteries = \App\Models\Lottery::where('draw_date', '<=', now())
            ->where('status', 'active')
            ->with('product')
            ->get();

        $this->info("📊 Found {$expiredLotteries->count()} expired lotteries");

        foreach ($expiredLotteries as $lottery) {
            $minParticipants = $lottery->product->min_participants ?? 10;
            $hasInsufficientParticipants = $lottery->sold_tickets < $minParticipants;

            if ($hasInsufficientParticipants) {
                $this->line("   🎯 {$lottery->lottery_number}: {$lottery->sold_tickets}/{$minParticipants} participants - WOULD REFUND");
            } else {
                $this->line("   ✅ {$lottery->lottery_number}: {$lottery->sold_tickets}/{$minParticipants} participants - OK");
            }
        }

        // Tombolas annulées
        $cancelledLotteries = \App\Models\Lottery::where('status', 'cancelled')
            ->whereDoesntHave('refunds')
            ->count();

        $this->info("📊 Found {$cancelledLotteries} cancelled lotteries without refunds");
    }

    /**
     * Afficher les statistiques de remboursements
     */
    protected function displayRefundStats()
    {
        $stats = $this->refundService->getRefundStats();

        $this->line('');
        $this->info('📈 Refund Statistics:');
        $this->line("   💰 Total refunds: {$stats['total_refunds']}");
        $this->line("   💸 Total amount: {$stats['total_amount_refunded']} FCFA");
        $this->line("   ⏳ Pending: {$stats['pending_refunds']} ({$stats['pending_amount']} FCFA)");
        $this->line("   🤖 Auto-processed: {$stats['auto_processed']}");
        $this->line("   👤 Manual: {$stats['manual_processed']}");

        if (!empty($stats['by_reason'])) {
            $this->line('');
            $this->info('📊 By Reason:');
            foreach ($stats['by_reason'] as $reason => $data) {
                $this->line("   🎯 {$reason}: {$data['count']} ({$data['total_amount']} FCFA)");
            }
        }
    }
}
