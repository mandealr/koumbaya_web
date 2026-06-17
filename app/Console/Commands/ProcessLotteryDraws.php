<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Services\LotteryDrawService;
use App\Services\RefundService;
use Illuminate\Support\Facades\Log;

class ProcessLotteryDraws extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lottery:draw {--lottery=* : Specific lottery IDs to process} {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic lottery draws for eligible lotteries';

    protected LotteryDrawService $drawService;

    protected RefundService $refundService;

    public function __construct(LotteryDrawService $drawService, RefundService $refundService)
    {
        parent::__construct();
        $this->drawService = $drawService;
        $this->refundService = $refundService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting lottery draw process...');

        $isDryRun = (bool) $this->option('dry-run');
        $specificLotteries = $this->option('lottery');

        if ($isDryRun) {
            $this->warn('Running in DRY RUN mode - no changes will be made');
        }

        // Tombolas candidates au tirage : actives et soit la date est atteinte,
        // soit tous les tickets sont vendus. On n'utilise que des colonnes
        // réelles (draw_date, sold_tickets, max_tickets) — l'éligibilité fine
        // (participants minimum, etc.) est déléguée au LotteryDrawService.
        $query = Lottery::with(['product', 'paidTickets'])
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('draw_date', '<=', now())
                    ->orWhereColumn('sold_tickets', '>=', 'max_tickets');
            });

        if (!empty($specificLotteries)) {
            $query->whereIn('id', $specificLotteries);
        }

        $eligibleLotteries = $query->get();

        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;

        if ($eligibleLotteries->isEmpty()) {
            $this->info('No eligible lotteries found for drawing.');
        } else {
            $this->info("Found {$eligibleLotteries->count()} candidate lotteries");

            foreach ($eligibleLotteries as $lottery) {
                try {
                    $this->processLottery($lottery, $isDryRun, $successCount, $failCount, $skippedCount);
                } catch (\Throwable $e) {
                    $failCount++;
                    $this->error("Failed to process lottery {$lottery->lottery_number}: " . $e->getMessage());
                    Log::error('Lottery draw failed', [
                        'lottery_id' => $lottery->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        // Remboursements automatiques des tombolas sous-souscrites / annulées
        // (le service applique son propre délai de grâce avant remboursement).
        $refundSummary = $this->processRefunds($isDryRun, $specificLotteries);

        // Résumé
        $this->info("\n" . str_repeat('=', 50));
        $this->info('Process completed!');
        $this->info("Draws — Success: $successCount | Failed: $failCount | Skipped: $skippedCount");
        $this->info("Refunds — Lotteries refunded: {$refundSummary['refunded']}");

        return Command::SUCCESS;
    }

    /**
     * Process a single lottery draw by delegating to the verifiable draw service.
     */
    protected function processLottery(Lottery $lottery, bool $isDryRun, int &$successCount, int &$failCount, int &$skippedCount): void
    {
        $this->info("\nProcessing lottery: {$lottery->lottery_number}");
        $this->info("Product: " . ($lottery->product->name ?? 'N/A'));

        $paidTicketsCount = $lottery->paidTickets()->count();
        $this->info("Paid tickets: $paidTicketsCount");

        if ($isDryRun) {
            $this->info("DRY RUN: would attempt draw via LotteryDrawService");
            $successCount++;
            return;
        }

        // Tirage vérifiable + enregistrement dans draw_histories + notifications,
        // assuré par le service (source de vérité unique, partagée avec l'API).
        $result = $this->drawService->performDraw($lottery, [
            'method' => 'auto',
            'initiated_by' => 'system',
        ]);

        if ($result['success']) {
            $successCount++;
            $winner = $result['data']['winning_ticket'] ?? null;
            $this->info('✓ Draw completed successfully'
                . ($winner ? " — winning ticket {$winner->ticket_number} (user {$winner->user_id})" : ''));
            return;
        }

        // Non éligible (ex. participants insuffisants) : on n'échoue pas, le
        // remboursement automatique sera traité par la passe dédiée après délai.
        $skippedCount++;
        $this->warn('Skipped: ' . ($result['message'] ?? 'not eligible for draw'));
    }

    /**
     * Trigger automatic refunds for under-subscribed / cancelled lotteries.
     *
     * @param  array<int|string>  $specificLotteries
     * @return array{refunded: int}
     */
    protected function processRefunds(bool $isDryRun, array $specificLotteries): array
    {
        // On ne lance la passe globale de remboursement que lors d'un run complet.
        if ($isDryRun || !empty($specificLotteries)) {
            return ['refunded' => 0];
        }

        try {
            $results = $this->refundService->checkAndProcessRefunds();
            $refunded = count($results['insufficient_participants'] ?? [])
                + count($results['cancelled_lotteries'] ?? []);

            if ($refunded > 0) {
                $this->info("✓ Automatic refunds processed for $refunded lottery(ies)");
            }

            return ['refunded' => $refunded];
        } catch (\Throwable $e) {
            $this->error('Automatic refund pass failed: ' . $e->getMessage());
            Log::error('Automatic refund pass failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['refunded' => 0];
        }
    }
}
