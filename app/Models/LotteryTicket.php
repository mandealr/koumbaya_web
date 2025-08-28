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
        'transaction_id',
        'price_paid',
        'payment_reference',
        'status',
        'is_winner',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'is_winner' => 'boolean',
            'purchased_at' => 'datetime',
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

    public function transaction()
    {
        return $this->belongsTo(Payment::class, 'transaction_id');
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
