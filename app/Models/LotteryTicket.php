<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LotteryTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'lottery_id',
        'user_id',
        'order_id',
        'payment_id',
        'price',
        'currency',
        'status',
        'is_winner',
        'purchased_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_winner' => 'boolean',
            'purchased_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * Relations
     */
    public function lottery()
    {
        return $this->belongsTo(Lottery::class, 'lottery_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Scopes
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    /**
     * Business Logic
     */
    public function generateTicketNumber()
    {
        return $this->lottery->lottery_number . '-T' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}
