<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lottery extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_number',
        'product_id',
        'total_tickets',
        'sold_tickets',
        'ticket_price',
        'draw_date',
        'winner_user_id',
        'winner_ticket_number',
        'status',
        'is_drawn',
        'draw_proof',
    ];

    protected function casts(): array
    {
        return [
            'ticket_price' => 'decimal:2',
            'draw_date' => 'datetime',
            'is_drawn' => 'boolean',
            'draw_proof' => 'json',
        ];
    }

    /**
     * Attributes that should be appended to arrays.
     */
    protected $appends = [
        'end_date', // Compatibilité backward
        'remaining_tickets',
        'progress_percentage',
        'is_expired',
        'can_draw',
        'time_remaining',
        'participation_rate',
        'is_ending_soon'
    ];

    /**
     * Relations
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function tickets()
    {
        return $this->hasMany(LotteryTicket::class, 'lottery_id');
    }

    public function paidTickets()
    {
        return $this->hasMany(LotteryTicket::class, 'lottery_id')->where('status', 'paid');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'lottery_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'lottery_id');
    }

    /**
     * Accesseurs pour compatibilité backward
     */
    public function getEndDateAttribute()
    {
        return $this->draw_date;
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['draw_date'] = $value;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpiredToday($query)
    {
        return $query->where('draw_date', '<=', now())
                    ->where('status', 'active');
    }

    /**
     * Accessors
     */
    public function getRemainingTicketsAttribute()
    {
        return $this->total_tickets - $this->sold_tickets;
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_tickets == 0) return 0;
        return round(($this->sold_tickets / $this->total_tickets) * 100, 2);
    }

    public function getIsExpiredAttribute()
    {
        return now() > $this->end_date;
    }

    public function getCanDrawAttribute()
    {
        // Vérifier si le produit existe et a min_participants
        if (!$this->product) {
            return false;
        }
        
        $minParticipants = $this->product->min_participants ?? 300;
        
        return $this->sold_tickets >= $minParticipants && 
               $this->status === 'active' && 
               !$this->is_drawn &&
               $this->end_date <= now(); // Peut être tirée seulement après la fin
    }

    /**
     * Business Logic
     */
    public function canPurchaseTicket()
    {
        return $this->status === 'active' && 
               $this->sold_tickets < $this->total_tickets && 
               !$this->is_expired;
    }

    public function getTotalRevenue()
    {
        return $this->sold_tickets * $this->ticket_price;
    }

    public function generateLotteryNumber()
    {
        return 'KMB-' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function isActive()
    {
        return $this->status === 'active' && 
               $this->end_date > now() &&
               $this->sold_tickets < $this->total_tickets;
    }

    /**
     * Temps restant avant la fin de la tombola
     */
    public function getTimeRemainingAttribute()
    {
        if ($this->end_date <= now()) {
            return null;
        }

        $now = now();
        return [
            'total_seconds' => $now->diffInSeconds($this->end_date),
            'days' => $now->diffInDays($this->end_date),
            'hours' => $now->diffInHours($this->end_date) % 24,
            'minutes' => $now->diffInMinutes($this->end_date) % 60,
            'seconds' => $now->diffInSeconds($this->end_date) % 60,
            'human' => $now->diffForHumans($this->end_date, true),
            'is_expired' => $this->is_expired
        ];
    }

    /**
     * Taux de participation (pourcentage de tickets vendus)
     */
    public function getParticipationRateAttribute()
    {
        if ($this->total_tickets == 0) return 0;
        return round(($this->sold_tickets / $this->total_tickets) * 100, 2);
    }

    /**
     * Vérifier si la tombola se termine bientôt (24h)
     */
    public function getIsEndingSoonAttribute()
    {
        $now = now();
        return $this->end_date <= $now->addHours(24) && 
               $this->end_date > $now && 
               $this->status === 'active';
    }

    /**
     * Méthodes utilitaires pour l'API mobile
     */
    public function toMobileArray()
    {
        return [
            'id' => $this->id,
            'lottery_number' => $this->lottery_number,
            'product_id' => $this->product_id,
            'total_tickets' => $this->total_tickets,
            'sold_tickets' => $this->sold_tickets,
            'remaining_tickets' => $this->remaining_tickets,
            'ticket_price' => (float) $this->ticket_price,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'draw_date' => $this->draw_date,
            'status' => $this->status,
            'is_drawn' => $this->is_drawn,
            'is_expired' => $this->is_expired,
            'can_draw' => $this->can_draw,
            'progress_percentage' => $this->progress_percentage,
            'participation_rate' => $this->participation_rate,
            'time_remaining' => $this->time_remaining,
            'is_ending_soon' => $this->is_ending_soon,
            'winner_user_id' => $this->winner_user_id,
            'winner_ticket_number' => $this->winner_ticket_number,
            'total_revenue' => $this->getTotalRevenue(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Vérifier si l'utilisateur peut participer à la tombola
     */
    public function canUserParticipate(?User $user = null)
    {
        if (!$this->canPurchaseTicket()) {
            return false;
        }
        
        // Ajouter ici d'autres vérifications si nécessaire
        // Par exemple, limite par utilisateur, etc.
        
        return true;
    }

    /**
     * Obtenir le nombre de tickets achetés par un utilisateur
     */
    public function getUserTicketCount(?User $user = null)
    {
        if (!$user) {
            return 0;
        }
        
        return $this->tickets()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->count();
    }
}
