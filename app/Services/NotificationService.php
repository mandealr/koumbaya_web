<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Lottery;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Notifier le gagnant d'une tombola
     */
    public function notifyLotteryWinner(Lottery $lottery, User $winner, $winningTicket)
    {
        try {
            // Créer la notification en base
            $notification = Notification::createWinnerNotification($lottery, $winner, $winningTicket);
            
            // Envoyer par email
            $this->sendWinnerEmail($winner, $lottery, $winningTicket);
            
            // Envoyer par SMS si disponible
            if ($winner->phone) {
                $this->sendWinnerSMS($winner, $lottery);
            }
            
            // Marquer comme envoyé
            $notification->markAsSent();
            
            Log::info('Winner notification sent', [
                'lottery_id' => $lottery->id,
                'winner_id' => $winner->id,
                'ticket_number' => $winningTicket->ticket_number
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to send winner notification', [
                'error' => $e->getMessage(),
                'lottery_id' => $lottery->id,
                'winner_id' => $winner->id
            ]);
            return false;
        }
    }

    /**
     * Notifier tous les participants du résultat du tirage
     */
    public function notifyLotteryResult(Lottery $lottery, User $winner = null)
    {
        try {
            // Créer les notifications pour tous les participants
            $notifications = Notification::createLotteryDrawNotification($lottery, $winner);
            
            // Envoyer les emails
            foreach ($notifications as $notification) {
                $participant = $notification->user;
                $isWinner = $notification->type === 'lottery_winner';
                
                if ($isWinner) {
                    $this->sendWinnerEmail($participant, $lottery, null);
                } else {
                    $this->sendLotteryResultEmail($participant, $lottery, $winner);
                }
                
                $notification->markAsSent();
            }
            
            Log::info('Lottery result notifications sent', [
                'lottery_id' => $lottery->id,
                'participants_count' => count($notifications)
            ]);

            return $notifications;
        } catch (\Exception $e) {
            Log::error('Failed to send lottery result notifications', [
                'error' => $e->getMessage(),
                'lottery_id' => $lottery->id
            ]);
            return false;
        }
    }

    /**
     * Notifier un achat de ticket
     */
    public function notifyTicketPurchase(User $user, Transaction $transaction)
    {
        try {
            // Créer la notification
            $notification = Notification::createTicketPurchaseNotification($user, $transaction);
            
            // Envoyer par email
            $this->sendPurchaseConfirmationEmail($user, $transaction);
            
            // Envoyer par SMS si disponible
            if ($user->phone) {
                $this->sendPurchaseConfirmationSMS($user, $transaction);
            }
            
            $notification->markAsSent();
            
            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to send purchase notification', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'transaction_id' => $transaction->id
            ]);
            return false;
        }
    }

    /**
     * Envoyer email de victoire
     */
    protected function sendWinnerEmail(User $winner, Lottery $lottery, $winningTicket = null)
    {
        $verificationCode = $this->generateVerificationCode($lottery);
        
        try {
            Mail::send('emails.lottery-winner', [
                'winner' => $winner,
                'lottery' => $lottery,
                'winningTicket' => $winningTicket,
                'verificationCode' => $verificationCode
            ], function ($message) use ($winner, $lottery) {
                $message->to($winner->email, $winner->full_name)
                        ->subject('🎉 Félicitations ! Vous avez gagné la tombola ' . $lottery->lottery_number);
            });
            
            Log::info('Winner email sent', [
                'winner_email' => $winner->email,
                'lottery_number' => $lottery->lottery_number
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send winner email', [
                'error' => $e->getMessage(),
                'winner_email' => $winner->email,
                'lottery_id' => $lottery->id
            ]);
        }
    }

    /**
     * Envoyer email de résultat de tirage
     */
    protected function sendLotteryResultEmail(User $user, Lottery $lottery, User $winner = null)
    {
        $isWinner = $winner && $winner->id === $user->id;
        
        try {
            Mail::send('emails.lottery-results', [
                'participant' => $user,
                'lottery' => $lottery,
                'winner' => $winner,
                'isWinner' => $isWinner
            ], function ($message) use ($user, $lottery, $isWinner) {
                $subject = $isWinner 
                    ? '🎉 Félicitations ! Vous avez gagné !' 
                    : '📊 Résultats de la tombola ' . $lottery->lottery_number;
                    
                $message->to($user->email, $user->full_name)
                        ->subject($subject);
            });
            
            Log::info('Lottery result email sent', [
                'participant_email' => $user->email,
                'lottery_number' => $lottery->lottery_number,
                'is_winner' => $isWinner
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send lottery result email', [
                'error' => $e->getMessage(),
                'participant_email' => $user->email,
                'lottery_id' => $lottery->id
            ]);
        }
    }

    /**
     * Envoyer email de confirmation d'achat
     */
    protected function sendPurchaseConfirmationEmail(User $user, Transaction $transaction)
    {
        try {
            $lottery = $transaction->lottery;
            $tickets = $transaction->tickets ?? [];
            
            Mail::send('emails.ticket-purchase', [
                'user' => $user,
                'transaction' => $transaction,
                'lottery' => $lottery,
                'tickets' => $tickets,
                'totalAmount' => $transaction->amount
            ], function ($message) use ($user, $transaction) {
                $message->to($user->email, $user->full_name)
                        ->subject('✅ Confirmation d\'achat de ticket - ' . $transaction->reference);
            });
            
            Log::info('Purchase confirmation email sent', [
                'user_email' => $user->email,
                'transaction_reference' => $transaction->reference
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send purchase confirmation email', [
                'error' => $e->getMessage(),
                'user_email' => $user->email,
                'transaction_id' => $transaction->id
            ]);
        }
    }

    /**
     * Envoyer SMS de victoire
     */
    protected function sendWinnerSMS(User $winner, Lottery $lottery)
    {
        $message = "🎉 Félicitations {$winner->first_name} ! Vous avez remporté {$lottery->product->title} dans la tombola {$lottery->lottery_number}. Consultez vos notifications pour plus de détails.";
        
        // Utiliser le service OTP pour envoyer le SMS
        $this->otpService->sendSMS($winner->phone, $message);
        
        Log::info('Winner SMS sent', [
            'phone' => $winner->phone,
            'lottery_id' => $lottery->id
        ]);
    }

    /**
     * Envoyer SMS de confirmation d'achat
     */
    protected function sendPurchaseConfirmationSMS(User $user, Transaction $transaction)
    {
        $message = "✅ Achat confirmé ! {$transaction->quantity} ticket(s) pour {$transaction->amount} FCFA. Tombola: {$transaction->lottery->lottery_number}. Bonne chance !";
        
        $this->otpService->sendSMS($user->phone, $message);
        
        Log::info('Purchase confirmation SMS sent', [
            'phone' => $user->phone,
            'transaction_id' => $transaction->id
        ]);
    }

    /**
     * Obtenir les notifications non lues d'un utilisateur
     */
    public function getUserUnreadNotifications(User $user, $limit = 20)
    {
        return $user->notifications()
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(User $user)
    {
        return $user->notifications()
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Obtenir les statistiques de notifications
     */
    public function getNotificationStats(User $user)
    {
        $notifications = $user->notifications();
        
        return [
            'total' => $notifications->count(),
            'unread' => $notifications->unread()->count(),
            'recent' => $notifications->recent(7)->count(),
            'by_type' => $notifications->select('type', \DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray()
        ];
    }
    
    /**
     * Générer un code de vérification unique pour une tombola
     */
    protected function generateVerificationCode(Lottery $lottery)
    {
        return strtoupper(substr(md5($lottery->id . $lottery->draw_date . $lottery->lottery_number), 0, 8));
    }
}