<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'reference',
        'user_id',
        'order_id',
        'transaction_id',
        'amount',
        'status',
        'payment_method',
        'ebilling_id',
        'callback_data',
        'paid_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'callback_data' => 'array',
            'meta' => 'array',
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
        // product_id est maintenant dans meta
        $productId = $this->meta['product_id'] ?? null;
        return $productId ? Product::find($productId) : null;
    }

    public function lottery()
    {
        // lottery_id est maintenant dans meta
        $lotteryId = $this->meta['lottery_id'] ?? null;
        return $lotteryId ? Lottery::find($lotteryId) : null;
    }

    public function lotteryTicket()
    {
        // lottery_ticket_id est maintenant dans meta
        $ticketId = $this->meta['lottery_ticket_id'] ?? null;
        return $ticketId ? LotteryTicket::find($ticketId) : null;
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
        return $this->hasManyThrough(Payment::class, Order::class, 'id', 'order_id', 'order_id', 'id');
    }

    /**
     * Accesseur pour type (compatibilité)
     */
    public function getTypeAttribute()
    {
        // Déterminer le type basé sur les relations
        if (isset($this->meta['lottery_id'])) {
            return 'lottery_ticket';
        } elseif (isset($this->meta['product_id'])) {
            return 'product_purchase';
        }
        return 'unknown';
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
        // type est maintenant dans meta
        return $query->whereJsonContains('meta->type', $type);
    }

    public function scopeTicketPurchases($query)
    {
        return $query->whereJsonContains('meta->type', 'ticket_purchase');
    }

    public function scopeDirectPurchases($query)
    {
        return $query->whereJsonContains('meta->type', 'direct_purchase');
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

    /**
     * Accesseurs pour les champs migrés vers meta
     */
    public function getTypeAttribute()
    {
        return $this->meta['type'] ?? null;
    }

    public function getCurrencyAttribute()
    {
        return $this->meta['currency'] ?? 'XAF';
    }

    public function getProductIdAttribute()
    {
        return $this->meta['product_id'] ?? null;
    }

    public function getLotteryIdAttribute()
    {
        return $this->meta['lottery_id'] ?? null;
    }

    public function getLotteryTicketIdAttribute()
    {
        return $this->meta['lottery_ticket_id'] ?? null;
    }

    public function getDescriptionAttribute()
    {
        return $this->meta['description'] ?? '';
    }

    public function getMetadataAttribute()
    {
        return $this->meta['metadata'] ?? [];
    }

    public function getQuantityAttribute()
    {
        return $this->meta['quantity'] ?? 1;
    }

    public function getPhoneNumberAttribute()
    {
        return $this->meta['phone_number'] ?? null;
    }

    public function getCompletedAtAttribute()
    {
        return $this->meta['completed_at'] ?? null;
    }

    public function getFailedAtAttribute()
    {
        return $this->meta['failed_at'] ?? null;
    }

    public function getFailureReasonAttribute()
    {
        return $this->meta['failure_reason'] ?? null;
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
        $meta = $this->meta ?? [];
        $meta['completed_at'] = now()->toISOString();
        
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
            'callback_data' => $callbackData,
            'meta' => $meta,
        ]);

        $this->tickets()->update(['status' => 'paid']);
        
        if ($this->lottery) {
            $this->lottery->increment('sold_tickets', $this->quantity ?? 1);
        }
    }

    public function markAsFailed($reason = null, array $callbackData = [])
    {
        $meta = $this->meta ?? [];
        $meta['failed_at'] = now()->toISOString();
        $meta['failure_reason'] = $reason;
        
        $this->update([
            'status' => 'failed',
            'callback_data' => $callbackData,
            'meta' => $meta,
        ]);

        $this->tickets()->update(['status' => 'refunded']);
    }
}
