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
        'start_date',
        'end_date',
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
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'draw_date' => 'datetime',
            'is_drawn' => 'boolean',
        ];
    }

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
        return $query->where('end_date', '<=', now())
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
        return ($this->sold_tickets / $this->total_tickets) * 100;
    }

    public function getIsExpiredAttribute()
    {
        return now() > $this->end_date;
    }

    public function getCanDrawAttribute()
    {
        return $this->sold_tickets >= $this->product->min_participants && 
               $this->status === 'active' && 
               !$this->is_drawn;
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
}
