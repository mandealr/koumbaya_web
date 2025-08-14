<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'transaction_id',
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
        'phone_number',
        'quantity',
        'payment_provider',
        'payment_provider_id',
        'callback_data',
        'description',
        'metadata',
        'paid_at',
        'completed_at',
        'failed_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'quantity' => 'integer',
            'metadata' => 'array',
            'callback_data' => 'json',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
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

    public function tickets()
    {
        return $this->hasMany(LotteryTicket::class);
    }
    
    public function lottery_tickets()
    {
        return $this->hasMany(LotteryTicket::class, 'transaction_id');
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

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'payment_initiated']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
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

    public function scopeExpired($query)
    {
        return $query->pending()
            ->where('created_at', '<', now()->subMinutes(15));
    }

    /**
     * Accessors
     */
    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsFailedAttribute()
    {
        return $this->status === 'failed';
    }

    public function getIsPendingAttribute()
    {
        return in_array($this->status, ['pending', 'payment_initiated']);
    }

    public function getExpiresAtAttribute()
    {
        if (!$this->is_pending) return null;
        
        return $this->created_at->addMinutes(15);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }

    /**
     * Business Logic
     */
    public function markAsCompleted(array $callbackData = [])
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'callback_data' => $callbackData,
        ]);

        $this->tickets()->update(['status' => 'paid']);
        
        if ($this->lottery) {
            $this->lottery->increment('sold_tickets', $this->quantity ?? 1);
        }
    }

    public function markAsFailed($reason = null, array $callbackData = [])
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
            'callback_data' => $callbackData,
        ]);

        $this->tickets()->update(['status' => 'cancelled']);
    }
}
