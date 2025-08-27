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
}
