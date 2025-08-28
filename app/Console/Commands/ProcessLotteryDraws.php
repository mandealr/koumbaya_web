<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
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

    /**
     * The notification service instance.
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting lottery draw process...');
        
        $isDryRun = $this->option('dry-run');
        $specificLotteries = $this->option('lottery');
        
        if ($isDryRun) {
            $this->warn('Running in DRY RUN mode - no changes will be made');
        }

        // Get eligible lotteries
        $query = Lottery::with(['product', 'paidTickets'])
            ->where('status', 'active')
            ->where('is_drawn', false)
            ->where('draw_date', '<=', now());

        // If specific lotteries are requested
        if (!empty($specificLotteries)) {
            $query->whereIn('id', $specificLotteries);
        }

        $eligibleLotteries = $query->get();

        if ($eligibleLotteries->isEmpty()) {
            $this->info('No eligible lotteries found for drawing.');
            return Command::SUCCESS;
        }

        $this->info("Found {$eligibleLotteries->count()} eligible lotteries");
        
        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;

        foreach ($eligibleLotteries as $lottery) {
            try {
                $this->processLottery($lottery, $isDryRun, $successCount, $failCount, $skippedCount);
            } catch (\Exception $e) {
                $failCount++;
                $this->error("Failed to process lottery {$lottery->lottery_number}: " . $e->getMessage());
                Log::error('Lottery draw failed', [
                    'lottery_id' => $lottery->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Summary
        $this->info("\n" . str_repeat('=', 50));
        $this->info("Process completed!");
        $this->info("Success: $successCount");
        $this->info("Failed: $failCount");
        $this->info("Skipped: $skippedCount");

        return Command::SUCCESS;
    }

    /**
     * Process a single lottery draw
     */
    protected function processLottery(Lottery $lottery, bool $isDryRun, int &$successCount, int &$failCount, int &$skippedCount)
    {
        $this->info("\nProcessing lottery: {$lottery->lottery_number}");
        $this->info("Product: {$lottery->product->title}");
        
        // Get paid tickets count
        $paidTicketsCount = $lottery->paidTickets()->count();
        $minParticipants = $lottery->product->min_participants ?? 300;
        
        $this->info("Participants: $paidTicketsCount / $minParticipants minimum required");

        // Check if minimum participants reached
        if ($paidTicketsCount < $minParticipants) {
            $this->warn("Skipped: Not enough participants (minimum: $minParticipants)");
            $skippedCount++;
            
            // Check if we should initiate refunds
            $daysSinceEnd = now()->diffInDays($lottery->end_date);
            if ($daysSinceEnd >= 3) {
                $this->warn("Lottery ended {$daysSinceEnd} days ago. Consider initiating refunds.");
                // TODO: Trigger refund process
            }
            
            return;
        }

        if ($isDryRun) {
            $this->info("DRY RUN: Would draw winner from $paidTicketsCount tickets");
            $successCount++;
            return;
        }

        // Perform the actual draw
        DB::beginTransaction();
        try {
            // Get all paid tickets
            $paidTickets = $lottery->paidTickets()->get();
            
            // Use secure random selection
            $winningTicket = $this->selectWinningTicket($paidTickets, $lottery);
            
            $this->info("Winner selected: Ticket {$winningTicket->ticket_number}");
            $this->info("Winner: User ID {$winningTicket->user_id}");

            // Mark ticket as winner
            $winningTicket->update(['is_winner' => true]);

            // Update lottery with winner information
            $lottery->update([
                'winner_user_id' => $winningTicket->user_id,
                'winner_ticket_number' => $winningTicket->ticket_number,
                'draw_date' => now(),
                'is_drawn' => true,
                'status' => 'completed',
                'draw_proof' => $this->generateDrawProof($lottery, $paidTickets, $winningTicket)
            ]);

            // Update product status
            $lottery->product->update(['status' => 'sold']);

            DB::commit();
            $successCount++;
            
            $this->info("✓ Draw completed successfully!");

            // Send notifications
            $this->sendNotifications($lottery, $winningTicket);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Select winning ticket using secure random method
     */
    protected function selectWinningTicket($tickets, Lottery $lottery)
    {
        // Generate cryptographically secure random number
        $totalTickets = $tickets->count();
        $randomIndex = random_int(0, $totalTickets - 1);
        
        // Additional entropy from system
        $seed = hash('sha256', $lottery->id . microtime(true) . random_bytes(32));
        $finalIndex = hexdec(substr($seed, 0, 8)) % $totalTickets;
        
        return $tickets->values()->get($finalIndex);
    }

    /**
     * Generate draw proof data
     */
    protected function generateDrawProof(Lottery $lottery, $paidTickets, $winningTicket)
    {
        return json_encode([
            'draw_method' => 'automatic_system_draw',
            'draw_algorithm' => 'secure_random_with_entropy',
            'total_participants' => $paidTickets->count(),
            'total_tickets' => $paidTickets->count(),
            'winning_ticket' => $winningTicket->ticket_number,
            'timestamp' => now()->toISOString(),
            'system_time' => microtime(true),
            'lottery_hash' => hash('sha256', $lottery->id . $lottery->lottery_number),
            'participants_hash' => hash('sha256', $paidTickets->pluck('id')->join(',')),
            'draw_triggered_by' => 'automated_cron',
            'server_info' => [
                'hostname' => gethostname(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version()
            ]
        ]);
    }

    /**
     * Send notifications to winner and participants
     */
    protected function sendNotifications(Lottery $lottery, LotteryTicket $winningTicket)
    {
        try {
            // Reload with relationships
            $lottery = $lottery->fresh(['product', 'winner']);
            $winner = $winningTicket->user;

            // Notify winner
            $this->notificationService->notifyLotteryWinner($lottery, $winner, $winningTicket);
            $this->info("✓ Winner notification sent");

            // Notify all participants
            $this->notificationService->notifyLotteryResult($lottery, $winner);
            $this->info("✓ Participant notifications sent");

        } catch (\Exception $e) {
            $this->error("Failed to send notifications: " . $e->getMessage());
            Log::error('Failed to send lottery notifications', [
                'lottery_id' => $lottery->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}