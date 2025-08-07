<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'type',
        'amount',
        'currency',
        'status',
        'payment_method',
        'external_transaction_id',
        'product_id',
        'lottery_id',
        'lottery_ticket_id',
        'description',
        'metadata',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function lottery()
    {
        return $this->belongsTo(Lottery::class, 'lottery_id');
    }

    public function lotteryTicket()
    {
        return $this->belongsTo(LotteryTicket::class, 'lottery_ticket_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'transaction_id');
    }

    /**
     * Scopes
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeTicketPurchases($query)
    {
        return $query->where('type', 'ticket_purchase');
    }

    public function scopeDirectPurchases($query)
    {
        return $query->where('type', 'direct_purchase');
    }
}
