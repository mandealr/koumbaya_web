<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\OrderStatus;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'type',
        'product_id',
        'lottery_id',
        'total_amount',
        'currency',
        'status',
        'payment_reference',
        'paid_at',
        'fulfilled_at',
        'cancelled_at',
        'refunded_at',
        'notes',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * Order types
     */
    const TYPE_LOTTERY = 'lottery';
    const TYPE_DIRECT = 'direct';

    /**
     * Order statuses (deprecated - use OrderStatus enum instead)
     */
    const STATUS_PENDING = 'pending';
    const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FULFILLED = 'fulfilled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_EXPIRED = 'expired';

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function lottery()
    {
        return $this->belongsTo(Lottery::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function tickets()
    {
        return $this->hasMany(LotteryTicket::class, 'order_id');
    }

    /**
     * Scopes
     */
    
    /**
     * Scope for paid orders
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PAID->value);
    }

    /**
     * Scope for pending orders
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PENDING->value);
    }

    /**
     * Scope for orders by user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accessors
     */
    
    /**
     * Check if order is paid
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === OrderStatus::PAID->value;
    }

    /**
     * Get order status as enum
     */
    public function getStatusEnumAttribute(): OrderStatus
    {
        return OrderStatus::from($this->status);
    }

    /**
     * Methods
     */
    
    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(uniqid());
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(string $paymentReference = null): bool
    {
        return $this->update([
            'status' => OrderStatus::PAID->value,
            'paid_at' => now(),
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * Mark order as fulfilled
     */
    public function markAsFulfilled(): bool
    {
        return $this->update([
            'status' => OrderStatus::FULFILLED->value,
            'fulfilled_at' => now(),
        ]);
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->status_enum->canBeCancelled();
    }

    /**
     * Check if order can be fulfilled
     */
    public function canBeFulfilled(): bool
    {
        return $this->status_enum->canBeFulfilled();
    }

    /**
     * Check if order has expired (1 hour after creation)
     */
    public function hasExpired(): bool
    {
        if ($this->status === self::STATUS_PAID || $this->status === self::STATUS_FULFILLED) {
            return false; // Les commandes payées/livrées ne peuvent pas expirer
        }

        return $this->created_at->addHour()->isPast();
    }

    /**
     * Check if payment can be retried
     */
    public function canRetryPayment(): bool
    {
        return !$this->hasExpired() && 
               in_array($this->status, [self::STATUS_PENDING, self::STATUS_AWAITING_PAYMENT, self::STATUS_FAILED]);
    }

    /**
     * Mark order as expired
     */
    public function markAsExpired(): bool
    {
        if (!$this->hasExpired()) {
            return false;
        }

        $updated = $this->update([
            'status' => self::STATUS_EXPIRED,
            'cancelled_at' => now(),
            'notes' => 'Commande expirée après 1 heure sans paiement'
        ]);

        if ($updated) {
            // Annuler les tickets réservés si c'est une commande tombola
            if ($this->type === self::TYPE_LOTTERY && $this->lottery_id) {
                \App\Models\LotteryTicket::where('payment_id', $this->payments()->first()?->id)
                    ->where('status', 'reserved')
                    ->update(['status' => 'cancelled']);
            }
        }

        return $updated;
    }

    /**
     * Scope pour les commandes expirées
     */
    public function scopeExpired($query)
    {
        return $query->where('status', '!=', self::STATUS_PAID)
                     ->where('status', '!=', self::STATUS_FULFILLED)
                     ->where('status', '!=', self::STATUS_EXPIRED)
                     ->where('created_at', '<', now()->subHour());
    }
}
