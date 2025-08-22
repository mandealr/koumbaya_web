<?php

namespace App\Services;

use App\Models\Refund;
use App\Models\Lottery;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RefundService
{
    protected $notificationService;
    protected $otpService;

    public function __construct(NotificationService $notificationService, OtpService $otpService)
    {
        $this->notificationService = $notificationService;
        $this->otpService = $otpService;
    }

    /**
     * Traiter les remboursements automatiques pour une tombola
     */
    public function processAutomaticRefunds(Lottery $lottery, string $reason): array
    {
        $refunds = [];
        $totalRefunded = 0;
        $participantCount = 0;

        try {
            DB::beginTransaction();

            // Récupérer toutes les transactions payées pour cette tombola
            $paidTransactions = Transaction::where('lottery_id', $lottery->id)
                ->where('status', 'completed')
                ->with('user')
                ->get();

            foreach ($paidTransactions as $transaction) {
                // Vérifier si un remboursement n'existe pas déjà
                $existingRefund = Refund::where('transaction_id', $transaction->id)->first();
                if ($existingRefund) {
                    continue;
                }

                // Créer le remboursement automatique
                $refund = Refund::createAutomaticRefund($transaction, $reason);

                // Traiter immédiatement le remboursement
                $this->processRefund($refund);

                $refunds[] = $refund;
                $totalRefunded += $refund->amount;
                $participantCount++;

                // Envoyer la notification de remboursement
                $this->notifyRefund($refund);
            }

            // Mettre à jour le statut de la tombola si nécessaire
            if ($reason === 'insufficient_participants') {
                $lottery->update(['status' => 'cancelled']);
            }

            DB::commit();

            Log::info('Automatic refunds processed', [
                'lottery_id' => $lottery->id,
                'lottery_number' => $lottery->lottery_number,
                'reason' => $reason,
                'participant_count' => $participantCount,
                'total_refunded' => $totalRefunded,
                'refunds_created' => count($refunds)
            ]);

            return [
                'success' => true,
                'refunds' => $refunds,
                'total_refunded' => $totalRefunded,
                'participant_count' => $participantCount
            ];
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Failed to process automatic refunds', [
                'lottery_id' => $lottery->id,
                'reason' => $reason,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier et traiter les tombolas qui nécessitent des remboursements
     */
    public function checkAndProcessRefunds(): array
    {
        $results = [
            'insufficient_participants' => [],
            'expired_lotteries' => [],
            'cancelled_lotteries' => []
        ];

        // 1. Tombolas expirées avec participants insuffisants
        $expiredLotteries = Lottery::where('end_date', '<=', now())
            ->where('status', 'active')
            ->with('product')
            ->get();

        foreach ($expiredLotteries as $lottery) {
            $minParticipants = $lottery->product->min_participants ?? 10;

            if ($lottery->sold_tickets < $minParticipants) {
                $result = $this->processAutomaticRefunds($lottery, 'insufficient_participants');
                $results['insufficient_participants'][] = [
                    'lottery' => $lottery,
                    'result' => $result
                ];
            }
        }

        // 2. Tombolas annulées manuellement
        $cancelledLotteries = Lottery::where('status', 'cancelled')
            ->whereDoesntHave('refunds')
            ->with('product')
            ->get();

        foreach ($cancelledLotteries as $lottery) {
            $result = $this->processAutomaticRefunds($lottery, 'lottery_cancelled');
            $results['cancelled_lotteries'][] = [
                'lottery' => $lottery,
                'result' => $result
            ];
        }

        return $results;
    }

    /**
     * Traiter un remboursement individuel
     */
    public function processRefund(Refund $refund): bool
    {
        try {
            // Pour l'instant, on simule le traitement du remboursement
            // Dans un environnement réel, ici on appellerait l'API de Mobile Money

            if ($refund->refund_method === 'mobile_money') {
                return $this->processMobileMoneyRefund($refund);
            } elseif ($refund->refund_method === 'wallet_credit') {
                return $this->processWalletCreditRefund($refund);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to process refund', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Traiter un remboursement Mobile Money
     */
    protected function processMobileMoneyRefund(Refund $refund): bool
    {
        try {
            $user = $refund->user;
            $transaction = $refund->transaction;

            // Simuler l'envoi du remboursement via Mobile Money
            // En production, intégrer avec l'API du fournisseur Mobile Money

            $refundData = [
                'amount' => $refund->amount,
                'phone' => $user->phone,
                'reference' => $refund->refund_number,
                'description' => 'Remboursement tombola ' . ($refund->lottery->lottery_number ?? 'N/A')
            ];

            // Simulation d'une réponse d'API réussie
            $apiResponse = [
                'success' => true,
                'transaction_id' => 'MM-REF-' . time(),
                'status' => 'completed',
                'timestamp' => now()->toISOString()
            ];

            // Marquer le remboursement comme traité
            $refund->process(null, $apiResponse);
            $refund->complete($apiResponse);

            Log::info('Mobile Money refund processed', [
                'refund_id' => $refund->id,
                'user_id' => $user->id,
                'amount' => $refund->amount,
                'phone' => $user->phone
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Mobile Money refund failed', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Traiter un remboursement par crédit de portefeuille
     */
    protected function processWalletCreditRefund(Refund $refund): bool
    {
        try {
            $user = $refund->user;

            // Créditer le portefeuille de l'utilisateur
            $wallet = $user->wallet()->firstOrCreate([
                'user_id' => $user->id
            ], [
                'balance' => 0,
                'currency' => 'XAF'
            ]);

            $wallet->increment('balance', $refund->amount);

            // Marquer le remboursement comme traité
            $refund->process(null, [
                'method' => 'wallet_credit',
                'wallet_id' => $wallet->id,
                'previous_balance' => $wallet->balance - $refund->amount,
                'new_balance' => $wallet->balance
            ]);
            $refund->complete();

            Log::info('Wallet credit refund processed', [
                'refund_id' => $refund->id,
                'user_id' => $user->id,
                'amount' => $refund->amount,
                'new_wallet_balance' => $wallet->balance
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Wallet credit refund failed', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Créer un remboursement manuel
     */
    public function createManualRefund(Transaction $transaction, string $reason, User $requestedBy = null): Refund
    {
        $refund = Refund::createManualRefund($transaction, $reason, $requestedBy);

        Log::info('Manual refund created', [
            'refund_id' => $refund->id,
            'transaction_id' => $transaction->id,
            'reason' => $reason,
            'requested_by' => $requestedBy?->id
        ]);

        return $refund;
    }

    /**
     * Approuver un remboursement
     */
    public function approveRefund(Refund $refund, User $approver, string $notes = null): bool
    {
        try {
            $refund->approve($approver, $notes);

            // Traiter le remboursement approuvé
            $processed = $this->processRefund($refund);

            if ($processed) {
                $this->notifyRefund($refund);
            }

            Log::info('Refund approved and processed', [
                'refund_id' => $refund->id,
                'approved_by' => $approver->id,
                'processed' => $processed
            ]);

            return $processed;
        } catch (\Exception $e) {
            Log::error('Failed to approve refund', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Rejeter un remboursement
     */
    public function rejectRefund(Refund $refund, User $rejector, string $reason): bool
    {
        try {
            $refund->reject($rejector, $reason);

            // Notifier l'utilisateur du rejet
            $this->notifyRefundRejection($refund);

            Log::info('Refund rejected', [
                'refund_id' => $refund->id,
                'rejected_by' => $rejector->id,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reject refund', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Notifier l'utilisateur du remboursement
     */
    protected function notifyRefund(Refund $refund): void
    {
        try {
            $user = $refund->user;
            $lottery = $refund->lottery;

            // Créer une notification en base
            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => 'refund_processed',
                'title' => 'Remboursement traité',
                'message' => "Votre remboursement de {$refund->amount} FCFA a été traité avec succès.",
                'data' => [
                    'refund_id' => $refund->id,
                    'refund_number' => $refund->refund_number,
                    'amount' => $refund->amount,
                    'reason' => $refund->reason,
                    'lottery_number' => $lottery?->lottery_number
                ],
                'related_type' => Refund::class,
                'related_id' => $refund->id,
                'sent_at' => now(),
                'status' => 'sent'
            ]);

            // Envoyer email
            $this->sendRefundEmail($refund);

            // Envoyer SMS si numéro disponible
            if ($user->phone) {
                $message = "✅ Remboursement traité: {$refund->amount} FCFA pour la tombola {$lottery?->lottery_number}. Référence: {$refund->refund_number}";
                $this->otpService->sendSMS($user->phone, $message);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify refund', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notifier l'utilisateur du rejet de remboursement
     */
    protected function notifyRefundRejection(Refund $refund): void
    {
        try {
            $user = $refund->user;

            // Créer une notification en base
            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => 'refund_rejected',
                'title' => 'Demande de remboursement rejetée',
                'message' => "Votre demande de remboursement a été rejetée. Raison: {$refund->rejection_reason}",
                'data' => [
                    'refund_id' => $refund->id,
                    'refund_number' => $refund->refund_number,
                    'amount' => $refund->amount,
                    'rejection_reason' => $refund->rejection_reason
                ],
                'related_type' => Refund::class,
                'related_id' => $refund->id,
                'sent_at' => now(),
                'status' => 'sent'
            ]);

            // Envoyer email de rejet
            $this->sendRefundRejectionEmail($refund);
        } catch (\Exception $e) {
            Log::error('Failed to notify refund rejection', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtenir les statistiques de remboursements
     */
    public function getRefundStats(): array
    {
        return [
            'total_refunds' => Refund::count(),
            'total_amount_refunded' => Refund::completed()->sum('amount'),
            'pending_refunds' => Refund::pending()->count(),
            'pending_amount' => Refund::pending()->sum('amount'),
            'auto_processed' => Refund::autoProcessed()->count(),
            'manual_processed' => Refund::where('auto_processed', false)->count(),
            'by_reason' => Refund::select('reason', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
                ->groupBy('reason')
                ->get()
                ->keyBy('reason')
                ->toArray(),
            'recent_refunds' => Refund::with(['user', 'lottery'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    /**
     * Envoyer email de remboursement traité
     */
    protected function sendRefundEmail(Refund $refund): void
    {
        try {
            $user = $refund->user;

            Mail::send('emails.refund-processed', [
                'refund' => $refund,
                'user' => $user
            ], function ($message) use ($user, $refund) {
                $message->to($user->email, $user->full_name)
                    ->subject('💸 Remboursement traité - ' . $refund->refund_number);
            });

            Log::info('Refund processed email sent', [
                'refund_id' => $refund->id,
                'user_email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send refund processed email', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer email de rejet de remboursement
     */
    protected function sendRefundRejectionEmail(Refund $refund): void
    {
        try {
            $user = $refund->user;

            Mail::send('emails.refund-rejected', [
                'refund' => $refund,
                'user' => $user
            ], function ($message) use ($user, $refund) {
                $message->to($user->email, $user->full_name)
                    ->subject('Demande de remboursement rejetée - ' . $refund->refund_number);
            });

            Log::info('Refund rejection email sent', [
                'refund_id' => $refund->id,
                'user_email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send refund rejection email', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
