<?php

namespace App\Services;

use App\Models\Refund;
use App\Models\Lottery;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Notification;
use App\Models\Payment;
use App\Services\ShapPayoutService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RefundService
{
    protected $notificationService;
    protected $otpService;
    protected $shapPayoutService;

    public function __construct(
        NotificationService $notificationService, 
        OtpService $otpService,
        ShapPayoutService $shapPayoutService
    ) {
        $this->notificationService = $notificationService;
        $this->otpService = $otpService;
        $this->shapPayoutService = $shapPayoutService;
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

            // Récupérer toutes les commandes payées pour cette tombola
            // Le remboursement se fait par commande (order), pas par paiement individuel
            $paidOrders = \App\Models\Order::where('lottery_id', $lottery->id)
                ->where('status', 'paid')
                ->with(['user'])
                ->get();

            foreach ($paidOrders as $order) {
                if (!$order->user) {
                    continue;
                }

                // Récupérer le premier paiement de cette commande pour la référence
                $firstPayment = Payment::where('order_id', $order->id)
                    ->where('lottery_id', $lottery->id)
                    ->first();

                if (!$firstPayment) {
                    continue;
                }

                // Vérifier si un remboursement n'existe pas déjà pour cette commande
                $existingRefund = Refund::where('transaction_id', $firstPayment->id)
                    ->orWhere(function($query) use ($order) {
                        $query->whereJsonContains('meta->order_id', $order->id);
                    })
                    ->first();

                if ($existingRefund) {
                    continue;
                }

                // Utiliser le montant total de la commande
                $totalAmountPaid = floatval($order->total_amount);

                if ($totalAmountPaid <= 0) {
                    continue;
                }

                // Calculer le montant à rembourser en retirant les frais Koumbaya (25%)
                // Le prix inclut: prix_base + commission (10%) + marge (15%)
                // Remboursement = montant_payé / 1.25
                $commissionRate = config('koumbaya.ticket_calculation.commission_rate', 0.10);
                $marginRate = config('koumbaya.ticket_calculation.margin_rate', 0.15);
                $koumbayaFeesRate = $commissionRate + $marginRate; // 0.25 (25%)

                $refundAmount = $totalAmountPaid / (1 + $koumbayaFeesRate);
                $koumbayaFees = $totalAmountPaid - $refundAmount;

                // Créer le remboursement automatique
                $refund = Refund::createAutomaticRefund($firstPayment, $reason);

                // Mettre à jour avec le montant calculé sans les frais Koumbaya
                $refund->update([
                    'amount' => round($refundAmount, 0),
                    'meta' => array_merge($refund->meta ?? [], [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'original_amount' => $totalAmountPaid,
                        'koumbaya_fees' => round($koumbayaFees, 0),
                        'koumbaya_fees_percentage' => $koumbayaFeesRate * 100,
                        'commission_rate' => $commissionRate,
                        'margin_rate' => $marginRate,
                    ])
                ]);

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
     * Vérifier et traiter les tombolas qui nécessitent des remboursements automatiques
     * Les tombolas expirées sont traitées après 24h, sauf traitement manuel avant
     */
    public function checkAndProcessRefunds(): array
    {
        $results = [
            'insufficient_participants' => [],
            'expired_lotteries' => [],
            'cancelled_lotteries' => []
        ];

        // 1. Tombolas expirées avec participants insuffisants (après délai de 24h)
        $delayHours = config('refund.auto_rules.insufficient_participants.delay_hours', 24);
        $refundDeadline = now()->subHours($delayHours);
        
        $expiredLotteries = Lottery::where('draw_date', '<=', $refundDeadline)
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
     * Vérifier les tombolas éligibles pour un remboursement (expirées mais pas encore traitées)
     */
    public function getEligibleLotteriesForRefund(): array
    {
        // Tombolas expirées avec participants insuffisants mais pas encore remboursées
        $expiredLotteries = Lottery::where('draw_date', '<=', now())
            ->where('status', 'active')
            ->with(['product', 'refunds'])
            ->get()
            ->filter(function ($lottery) {
                // Vérifier les participants insuffisants
                $minParticipants = $lottery->product->min_participants 
                    ?? config('refund.thresholds.min_participants_default', 10);
                
                // Vérifier qu'aucun remboursement n'a été traité
                $hasRefunds = $lottery->refunds()->exists();
                
                return $lottery->sold_tickets < $minParticipants && !$hasRefunds;
            });

        return $expiredLotteries->map(function ($lottery) {
            $minParticipants = $lottery->product->min_participants 
                ?? config('refund.thresholds.min_participants_default', 10);
            
            $delayHours = config('refund.auto_rules.insufficient_participants.delay_hours', 24);
            $autoRefundTime = $lottery->draw_date->addHours($delayHours);
            $canProcessManually = config('refund.auto_rules.insufficient_participants.allow_manual_before_delay', true);
            
            return [
                'lottery' => $lottery,
                'min_participants' => $minParticipants,
                'current_participants' => $lottery->sold_tickets,
                'auto_refund_time' => $autoRefundTime,
                'can_process_now' => now()->gte($autoRefundTime),
                'can_process_manually' => $canProcessManually && now()->lt($autoRefundTime),
                'hours_until_auto' => now()->lt($autoRefundTime) ? now()->diffInHours($autoRefundTime) : 0,
            ];
        })->toArray();
    }

    /**
     * Forcer le remboursement d'une tombola avant le délai automatique
     */
    public function forceRefundLottery(Lottery $lottery, string $reason = 'manual_force'): array
    {
        // Vérifier que la tombola est éligible
        $minParticipants = $lottery->product->min_participants 
            ?? config('refund.thresholds.min_participants_default', 10);
            
        if ($lottery->status !== 'active') {
            throw new \Exception('Cette tombola n\'est pas active');
        }
        
        if ($lottery->draw_date > now()) {
            throw new \Exception('Cette tombola n\'est pas encore expirée');
        }
        
        if ($lottery->sold_tickets >= $minParticipants) {
            throw new \Exception('Cette tombola a suffisamment de participants');
        }
        
        if ($lottery->refunds()->exists()) {
            throw new \Exception('Des remboursements ont déjà été traités pour cette tombola');
        }
        
        // Traiter les remboursements
        $result = $this->processAutomaticRefunds($lottery, $reason);
        
        Log::info('Manual lottery refund forced', [
            'lottery_id' => $lottery->id,
            'lottery_number' => $lottery->lottery_number,
            'participants' => $lottery->sold_tickets,
            'min_participants' => $minParticipants,
            'forced_by' => auth()->id(),
            'result' => $result
        ]);
        
        return $result;
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

            // Valider les données utilisateur
            if (!$user->phone) {
                throw new \Exception('User phone number is missing');
            }

            // Détecter l'opérateur basé sur le numéro de téléphone
            $operator = $this->shapPayoutService->detectOperatorFromPhone($user->phone);

            // Valider les données du payout
            $validationErrors = $this->shapPayoutService->validatePayoutData($user->phone, $refund->amount);
            if (!empty($validationErrors)) {
                throw new \Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            Log::info('Processing SHAP Mobile Money refund', [
                'refund_id' => $refund->id,
                'refund_number' => $refund->refund_number,
                'user_id' => $user->id,
                'phone' => $user->phone,
                'amount' => $refund->amount,
                'operator' => $operator
            ]);

            // Créer le payout via SHAP API
            $payoutResponse = $this->shapPayoutService->createPayout(
                $operator,
                $user->phone,
                $refund->amount,
                $refund->refund_number,
                'refund'
            );

            Log::info('SHAP Mobile Money refund processed successfully', [
                'refund_id' => $refund->id,
                'payout_id' => $payoutResponse['payout_id'],
                'transaction_id' => $payoutResponse['transaction_id'],
                'operator' => $operator,
                'amount' => $refund->amount,
                'phone' => $user->phone,
                'state' => $payoutResponse['state']
            ]);

            // Marquer le remboursement comme traité
            $refund->process(null, [
                'shap_payout_id' => $payoutResponse['payout_id'],
                'shap_transaction_id' => $payoutResponse['transaction_id'],
                'operator' => $operator,
                'state' => $payoutResponse['state'],
                'timestamp' => now()->toISOString()
            ]);

            // Si le payout est en succès, marquer comme complété
            if (in_array($payoutResponse['state'], ['success', 'completed'])) {
                $refund->complete([
                    'completed_at' => now()->toISOString()
                ]);
            }

            // Stocker l'ID externe pour référence
            $refund->update([
                'external_refund_id' => $payoutResponse['payout_id'],
                'notes' => ($refund->notes ? $refund->notes . "\n" : '') . "Processed via SHAP API with operator: {$operator}"
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('SHAP Mobile Money refund failed', [
                'refund_id' => $refund->id,
                'user_phone' => $user->phone ?? 'N/A',
                'amount' => $refund->amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $refund->update([
                'status' => 'rejected',
                'rejection_reason' => 'SHAP API Error: ' . $e->getMessage(),
                'rejected_at' => now(),
                'notes' => ($refund->notes ? $refund->notes . "\n" : '') . "Failed via SHAP API: {$e->getMessage()}"
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
    public function createManualRefund(Payment $transaction, string $reason, User $requestedBy = null): Refund
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

    /**
     * Calculer le montant du remboursement après déduction des frais administratifs
     *
     * @param float $originalAmount Montant original de la transaction
     * @param string $reason Raison du remboursement
     * @return array Détails du calcul
     */
    protected function calculateRefundAmount(float $originalAmount, string $reason): array
    {
        $config = config('refund.fees');

        // Vérifier si les frais doivent être appliqués
        $applyFees = $config['apply_admin_fees'] ?? true;
        $feeExemptReasons = $config['fee_exempt_reasons'] ?? [];

        // Ne pas appliquer de frais pour les raisons exemptées
        if (!$applyFees || in_array($reason, $feeExemptReasons)) {
            return [
                'refund_amount' => $originalAmount,
                'admin_fee' => 0,
                'admin_fee_percentage' => 0,
                'fee_applied' => false,
            ];
        }

        // Calculer les frais administratifs
        $adminFeePercentage = $config['admin_fee_percentage'] ?? 5;
        $adminFeeFixed = $config['admin_fee_fixed'] ?? 0;

        // Calculer les frais en pourcentage
        $adminFee = ($originalAmount * $adminFeePercentage / 100) + $adminFeeFixed;

        // Calculer le montant du remboursement
        $refundAmount = $originalAmount - $adminFee;

        // Vérifier le montant minimum après frais
        $minAmountAfterFees = $config['min_amount_after_fees'] ?? 50;
        if ($refundAmount < $minAmountAfterFees) {
            // Si le montant est trop faible, rembourser le montant original sans frais
            Log::warning('Refund amount after fees too low, refunding full amount', [
                'original_amount' => $originalAmount,
                'calculated_refund' => $refundAmount,
                'min_required' => $minAmountAfterFees
            ]);

            return [
                'refund_amount' => $originalAmount,
                'admin_fee' => 0,
                'admin_fee_percentage' => 0,
                'fee_applied' => false,
                'reason' => 'amount_too_low_for_fees',
            ];
        }

        return [
            'refund_amount' => round($refundAmount, 2),
            'admin_fee' => round($adminFee, 2),
            'admin_fee_percentage' => $adminFeePercentage,
            'fee_applied' => true,
        ];
    }
}
