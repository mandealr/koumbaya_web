<?php

namespace App\Services;

use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\DrawHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class LotteryDrawService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Perform a lottery draw
     */
    public function performDraw(Lottery $lottery, array $options = []): array
    {
        // Validate lottery can be drawn
        $validation = $this->validateDrawEligibility($lottery);
        if (!$validation['eligible']) {
            return [
                'success' => false,
                'message' => $validation['reason'],
                'data' => $validation
            ];
        }

        DB::beginTransaction();
        try {
            // Get all paid tickets
            $paidTickets = $lottery->paidTickets()->with('user')->get();
            
            // Generate draw seed for transparency
            $drawSeed = $this->generateDrawSeed($lottery, $paidTickets);
            
            // Select winner using verifiable random
            $winningTicket = $this->selectWinnerWithVerifiableRandom($paidTickets, $drawSeed);
            
            // Record draw history
            $drawHistory = $this->recordDrawHistory($lottery, $winningTicket, $paidTickets, $drawSeed, $options);
            
            // Update winning ticket
            $winningTicket->update(['is_winner' => true]);
            
            // Update lottery
            $lottery->update([
                'winner_user_id' => $winningTicket->user_id,
                'winner_ticket_number' => $winningTicket->ticket_number,
                'draw_date' => now(),
                'is_drawn' => true,
                'status' => 'completed',
                'draw_proof' => $this->generateDrawProof($lottery, $paidTickets, $winningTicket, $drawSeed, $drawHistory)
            ]);
            
            // Update product status
            $lottery->product->update(['status' => 'sold']);
            
            DB::commit();
            
            // Send notifications (outside transaction)
            $this->sendDrawNotifications($lottery, $winningTicket);
            
            return [
                'success' => true,
                'message' => 'Draw completed successfully',
                'data' => [
                    'lottery' => $lottery->fresh(['winner', 'product']),
                    'winning_ticket' => $winningTicket->fresh('user'),
                    'draw_history' => $drawHistory,
                    'verification_hash' => $drawHistory->verification_hash ?? null
                ]
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lottery draw failed', [
                'lottery_id' => $lottery->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Draw failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate if lottery is eligible for draw
     */
    protected function validateDrawEligibility(Lottery $lottery): array
    {
        // Already drawn?
        if ($lottery->is_drawn) {
            return ['eligible' => false, 'reason' => 'Lottery has already been drawn'];
        }
        
        // Status check
        if ($lottery->status !== 'active') {
            return ['eligible' => false, 'reason' => 'Lottery is not active'];
        }
        
        // End date check
        if ($lottery->end_date > now()) {
            return ['eligible' => false, 'reason' => 'Lottery end date not reached'];
        }
        
        // Minimum participants check
        $paidTicketsCount = $lottery->paidTickets()->count();
        $minParticipants = $lottery->product->min_participants ?? 300;
        
        if ($paidTicketsCount < $minParticipants) {
            return [
                'eligible' => false, 
                'reason' => "Not enough participants ($paidTicketsCount/$minParticipants)",
                'participants' => $paidTicketsCount,
                'required' => $minParticipants
            ];
        }
        
        return ['eligible' => true, 'reason' => 'All conditions met'];
    }

    /**
     * Generate deterministic seed for draw
     */
    protected function generateDrawSeed(Lottery $lottery, Collection $tickets): string
    {
        // Create deterministic seed from:
        // 1. Lottery data
        // 2. All ticket IDs (order matters)
        // 3. Current timestamp
        // 4. Additional entropy
        
        $lotteryData = $lottery->id . '|' . $lottery->lottery_number . '|' . $lottery->created_at;
        $ticketData = $tickets->pluck('id')->sort()->join(',');
        $timestamp = now()->getPreciseTimestamp(6); // Microsecond precision
        $entropy = random_bytes(32);
        
        $seedData = $lotteryData . '|' . $ticketData . '|' . $timestamp . '|' . bin2hex($entropy);
        
        return hash('sha512', $seedData);
    }

    /**
     * Select winner using verifiable random
     */
    protected function selectWinnerWithVerifiableRandom(Collection $tickets, string $seed): LotteryTicket
    {
        $totalTickets = $tickets->count();
        
        // Convert seed to number using multiple rounds
        $rounds = 3;
        $combinedHash = $seed;
        
        for ($i = 0; $i < $rounds; $i++) {
            $combinedHash = hash('sha256', $combinedHash . $i);
        }
        
        // Take first 16 chars and convert to decimal
        $hashNumber = hexdec(substr($combinedHash, 0, 16));
        
        // Get index
        $winningIndex = $hashNumber % $totalTickets;
        
        return $tickets->values()->get($winningIndex);
    }

    /**
     * Record draw in history table
     */
    protected function recordDrawHistory($lottery, $winningTicket, $paidTickets, $seed, $options)
    {
        return DrawHistory::create([
            'lottery_id' => $lottery->id,
            'winning_ticket_id' => $winningTicket->id,
            'winner_user_id' => $winningTicket->user_id,
            'total_participants' => $paidTickets->count(),
            'total_tickets' => $paidTickets->count(),
            'draw_method' => $options['method'] ?? 'automatic',
            'initiated_by' => $options['initiated_by'] ?? 'system',
            'draw_seed' => $seed,
            'verification_hash' => hash('sha256', $lottery->id . $winningTicket->id . $seed),
            'participant_snapshot' => $paidTickets->pluck('ticket_number')->sort()->values()->toArray(),
            'metadata' => [
                'ticket_ids' => $paidTickets->pluck('id')->sort()->values(),
                'server_time' => microtime(true),
                'php_version' => PHP_VERSION,
                'server_name' => gethostname()
            ],
            'drawn_at' => now()
        ]);
    }

    /**
     * Generate comprehensive draw proof
     */
    protected function generateDrawProof($lottery, $paidTickets, $winningTicket, $seed, $drawHistory)
    {
        return json_encode([
            'draw_method' => 'verifiable_random_selection',
            'algorithm' => 'sha512_seed_with_sha256_rounds',
            'seed' => $seed,
            'seed_components' => [
                'lottery_id' => $lottery->id,
                'ticket_count' => $paidTickets->count(),
                'timestamp' => now()->toISOString()
            ],
            'participants' => [
                'total' => $paidTickets->count(),
                'ticket_numbers' => $paidTickets->pluck('ticket_number')->sort()->values()
            ],
            'winner' => [
                'ticket_number' => $winningTicket->ticket_number,
                'ticket_id' => $winningTicket->id,
                'user_id' => $winningTicket->user_id
            ],
            'verification' => [
                'hash' => $drawHistory['verification_hash'],
                'can_verify_at' => url("/api/lottery/{$lottery->id}/verify-draw")
            ],
            'metadata' => [
                'drawn_at' => now()->toISOString(),
                'server' => gethostname(),
                'process' => $drawHistory['draw_method']
            ]
        ]);
    }

    /**
     * Send all necessary notifications
     */
    protected function sendDrawNotifications(Lottery $lottery, LotteryTicket $winningTicket)
    {
        try {
            $lottery = $lottery->fresh(['product', 'winner']);
            $winner = $winningTicket->user;
            
            // Notify winner
            $this->notificationService->notifyLotteryWinner($lottery, $winner, $winningTicket);
            
            // Notify all participants
            $this->notificationService->notifyLotteryResult($lottery, $winner);
            
            // Notify merchant
            if ($lottery->product->merchant) {
                $this->notificationService->notifyMerchantOfWinner($lottery, $winner);
            }
            
            Log::info('Lottery draw notifications sent', [
                'lottery_id' => $lottery->id,
                'winner_id' => $winner->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send draw notifications', [
                'lottery_id' => $lottery->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get eligible lotteries for automatic draw
     */
    public function getEligibleLotteries(): Collection
    {
        return Lottery::with(['product', 'paidTickets'])
            ->where('status', 'active')
            ->where('is_drawn', false)
            ->where('end_date', '<=', now())
            ->get()
            ->filter(function ($lottery) {
                $validation = $this->validateDrawEligibility($lottery);
                return $validation['eligible'];
            });
    }

    /**
     * Verify a draw result
     */
    public function verifyDraw(Lottery $lottery): array
    {
        if (!$lottery->is_drawn || !$lottery->draw_proof) {
            return [
                'valid' => false,
                'message' => 'No draw record found'
            ];
        }
        
        $proof = json_decode($lottery->draw_proof, true);
        
        // Recreate the draw conditions
        $paidTickets = $lottery->paidTickets()->orderBy('id')->get();
        $seed = $proof['seed'] ?? null;
        
        if (!$seed) {
            return [
                'valid' => false,
                'message' => 'No seed found in draw proof'
            ];
        }
        
        // Recalculate winner
        $recalculatedWinner = $this->selectWinnerWithVerifiableRandom($paidTickets, $seed);
        
        $isValid = $recalculatedWinner->ticket_number === $lottery->winner_ticket_number;
        
        return [
            'valid' => $isValid,
            'message' => $isValid ? 'Draw verified successfully' : 'Draw verification failed',
            'data' => [
                'recorded_winner' => $lottery->winner_ticket_number,
                'calculated_winner' => $recalculatedWinner->ticket_number,
                'seed' => $seed,
                'participants' => $paidTickets->count()
            ]
        ];
    }
}