<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'sent_at',
        'channel',
        'status',
        'related_type',
        'related_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'json',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Accessors
     */
    public function getIsReadAttribute()
    {
        return $this->read_at !== null;
    }

    public function getIsSentAttribute()
    {
        return $this->sent_at !== null;
    }

    /**
     * Business Logic
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update(['read_at' => now()]);
        }
        return $this;
    }

    public function markAsSent()
    {
        if (!$this->is_sent) {
            $this->update(['sent_at' => now(), 'status' => 'sent']);
        }
        return $this;
    }

    /**
     * Static methods for creating notifications
     */
    public static function createWinnerNotification($lottery, $winner, $ticket)
    {
        return self::create([
            'user_id' => $winner->id,
            'type' => 'lottery_winner',
            'title' => '🎉 Félicitations ! Vous avez gagné !',
            'message' => "Vous avez remporté la tombola {$lottery->lottery_number} avec le ticket {$ticket->ticket_number}. Votre prix : {$lottery->product->title}",
            'data' => [
                'lottery_id' => $lottery->id,
                'lottery_number' => $lottery->lottery_number,
                'product_title' => $lottery->product->title,
                'product_image' => $lottery->product->image_url,
                'ticket_number' => $ticket->ticket_number,
                'prize_value' => $lottery->product->price,
            ],
            'channel' => 'app,email',
            'status' => 'pending',
            'related_type' => Lottery::class,
            'related_id' => $lottery->id,
        ]);
    }

    public static function createLotteryDrawNotification($lottery, $winner = null)
    {
        // Notification pour tous les participants
        $participants = User::whereHas('lotteryTickets', function ($query) use ($lottery) {
            $query->where('lottery_id', $lottery->id)
                  ->where('status', 'paid');
        })->get();

        $notifications = [];

        foreach ($participants as $participant) {
            $isWinner = $winner && $winner->id === $participant->id;
            
            $notifications[] = self::create([
                'user_id' => $participant->id,
                'type' => $isWinner ? 'lottery_winner' : 'lottery_draw_result',
                'title' => $isWinner ? '🎉 Vous avez gagné !' : '📋 Résultat de la tombola',
                'message' => $isWinner 
                    ? "Félicitations ! Vous avez remporté {$lottery->product->title} !"
                    : "Le tirage de la tombola {$lottery->lottery_number} a eu lieu. Consultez les résultats.",
                'data' => [
                    'lottery_id' => $lottery->id,
                    'lottery_number' => $lottery->lottery_number,
                    'product_title' => $lottery->product->title,
                    'is_winner' => $isWinner,
                    'winner_name' => $winner ? $winner->first_name . ' ' . $winner->last_name : null,
                ],
                'channel' => 'app,email',
                'status' => 'pending',
                'related_type' => Lottery::class,
                'related_id' => $lottery->id,
            ]);
        }

        return $notifications;
    }

    public static function createTicketPurchaseNotification($user, $transaction)
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'ticket_purchase',
            'title' => '✅ Achat confirmé',
            'message' => "Votre achat de {$transaction->quantity} ticket(s) a été confirmé pour {$transaction->amount} FCFA.",
            'data' => [
                'transaction_id' => $transaction->transaction_id,
                'quantity' => $transaction->quantity,
                'amount' => $transaction->amount,
                'lottery_number' => $transaction->lottery->lottery_number,
                'product_title' => $transaction->lottery->product->title,
            ],
            'channel' => 'app,sms',
            'status' => 'pending',
            'related_type' => Transaction::class,
            'related_id' => $transaction->id,
        ]);
    }
}