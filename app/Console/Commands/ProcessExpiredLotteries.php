<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Services\RefundService;
use Illuminate\Support\Facades\Log;

class ProcessExpiredLotteries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lottery:process-expired 
                            {--dry-run : Run in dry-run mode without making changes}
                            {--force : Force processing even before 24h delay}
                            {--lottery= : Process specific lottery ID}';

    /**
     * The console command description.
     */
    protected $description = 'Process expired lotteries and handle refunds after 24h delay';

    protected $refundService;

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
        $this->info('🔄 Processing expired lotteries for refunds...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $lotteryId = $this->option('lottery');

        if ($dryRun) {
            $this->warn('⚠️  Running in DRY-RUN mode - no changes will be made');
            $this->newLine();
        }

        if ($force) {
            $this->warn('⚠️  FORCE mode enabled - processing before 24h delay');
            $this->newLine();
        }

        try {
            if ($lotteryId) {
                // Traiter une tombola spécifique
                $this->processSingleLottery($lotteryId, $dryRun, $force);
            } else {
                // Traitement global
                $this->processAllEligibleLotteries($dryRun, $force);
            }

            $this->newLine();
            $this->info('✅ Processing completed successfully');
            
        } catch (\Exception $e) {
            $this->error('❌ Error during processing: ' . $e->getMessage());
            Log::error('ProcessExpiredLotteries command failed', [
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
        $lottery = Lottery::with(['product', 'refunds'])->find($lotteryId);
        
        if (!$lottery) {
            $this->error("Lottery ID {$lotteryId} not found");
            return;
        }

        $this->info("Processing Lottery: {$lottery->lottery_number}");
        $this->info("Product: {$lottery->product->title}");
        $this->info("Participants: {$lottery->sold_tickets}");
        
        $minParticipants = $lottery->product->min_participants 
            ?? config('refund.thresholds.min_participants_default', 10);
        
        $this->info("Minimum required: {$minParticipants}");
        $this->info("Status: {$lottery->status}");
        $this->info("Draw date: {$lottery->draw_date}");

        // Vérifier l'éligibilité
        if ($lottery->status !== 'active') {
            $this->warn("⚠️  Lottery is not active (status: {$lottery->status})");
            return;
        }

        if ($lottery->draw_date > now()) {
            $this->warn("⚠️  Lottery has not expired yet");
            return;
        }

        if ($lottery->sold_tickets >= $minParticipants) {
            $this->warn("⚠️  Lottery has sufficient participants");
            return;
        }

        if ($lottery->refunds()->exists()) {
            $this->warn("⚠️  Refunds already exist for this lottery");
            return;
        }

        // Vérifier le délai de 24h
        $delayHours = config('refund.auto_rules.insufficient_participants.delay_hours', 24);
        $refundTime = $lottery->draw_date->addHours($delayHours);
        $canProcess = $force || now()->gte($refundTime);

        if (!$canProcess) {
            $hoursLeft = now()->diffInHours($refundTime);
            $this->warn("⚠️  Cannot process yet - {$hoursLeft} hours remaining until auto-processing");
            $this->info("Auto-refund time: {$refundTime}");
            return;
        }

        if ($dryRun) {
            $estimatedAmount = $lottery->sold_tickets * $lottery->ticket_price;
            $this->info("📋 Would process refund for {$lottery->sold_tickets} participants");
            $this->info("💰 Estimated refund amount: " . number_format($estimatedAmount) . " FCFA");
            return;
        }

        // Traiter le remboursement
        $this->info("💸 Processing refunds...");
        
        if ($force) {
            $result = $this->refundService->forceRefundLottery($lottery, 'manual_force_command');
        } else {
            $result = $this->refundService->processAutomaticRefunds($lottery, 'insufficient_participants');
        }

        if ($result['success']) {
            $this->info("✅ Refunds processed successfully");
            $this->info("   - Participants refunded: {$result['participant_count']}");
            $this->info("   - Total amount: " . number_format($result['total_refunded']) . " FCFA");
            $this->info("   - Refunds created: " . count($result['refunds']));
        } else {
            $this->error("❌ Failed to process refunds: " . $result['error']);
        }
    }

    /**
     * Traiter toutes les tombolas éligibles
     */
    protected function processAllEligibleLotteries(bool $dryRun, bool $force)
    {
        if ($force) {
            // En mode force, récupérer toutes les tombolas éligibles
            $eligibleLotteries = $this->refundService->getEligibleLotteriesForRefund();
            
            $this->info("Found " . count($eligibleLotteries) . " eligible lotteries");
            
            if ($dryRun) {
                $this->displayEligibleLotteries($eligibleLotteries, true);
                return;
            }

            foreach ($eligibleLotteries as $item) {
                $lottery = $item['lottery'];
                $this->info("Processing lottery: {$lottery->lottery_number}");
                
                try {
                    $result = $this->refundService->forceRefundLottery($lottery, 'manual_force_command');
                    if ($result['success']) {
                        $this->info("✅ Processed {$result['participant_count']} refunds");
                    }
                } catch (\Exception $e) {
                    $this->error("❌ Failed: " . $e->getMessage());
                }
            }
        } else {
            // Mode normal - seulement les tombolas prêtes (après 24h)
            if ($dryRun) {
                $this->info("📋 DRY-RUN: Checking what would be processed...");
            }

            $results = $this->refundService->checkAndProcessRefunds();
            
            $this->info("Processing results:");
            $this->info("- Insufficient participants: " . count($results['insufficient_participants']));
            $this->info("- Cancelled lotteries: " . count($results['cancelled_lotteries']));

            foreach ($results['insufficient_participants'] as $item) {
                $lottery = $item['lottery'];
                $result = $item['result'];
                
                if ($result['success']) {
                    $this->info("✅ {$lottery->lottery_number}: {$result['participant_count']} refunds");
                } else {
                    $this->error("❌ {$lottery->lottery_number}: {$result['error']}");
                }
            }
        }
    }

    /**
     * Afficher les tombolas éligibles
     */
    protected function displayEligibleLotteries(array $eligibleLotteries, bool $includeTimings = false)
    {
        if (empty($eligibleLotteries)) {
            $this->info("No eligible lotteries found");
            return;
        }

        $headers = ['Lottery', 'Product', 'Participants', 'Min Required', 'Deficit', 'Expired'];
        if ($includeTimings) {
            $headers[] = 'Can Process Now';
            $headers[] = 'Hours Until Auto';
        }

        $rows = [];
        foreach ($eligibleLotteries as $item) {
            $lottery = $item['lottery'];
            
            $row = [
                $lottery->lottery_number,
                substr($lottery->product->title, 0, 30) . '...',
                $item['current_participants'],
                $item['min_participants'],
                $item['min_participants'] - $item['current_participants'],
                $lottery->draw_date->diffForHumans(),
            ];

            if ($includeTimings) {
                $row[] = $item['can_process_now'] ? '✅' : '❌';
                $row[] = $item['can_process_now'] ? '0' : $item['hours_until_auto'];
            }

            $rows[] = $row;
        }

        $this->table($headers, $rows);
    }
}