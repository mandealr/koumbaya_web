<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_number',
        'user_id',
        'transaction_id',
        'lottery_id',
        'amount',
        'currency',
        'reason',
        'type',
        'status',
        'processed_at',
        'processed_by',
        'refund_method',
        'external_refund_id',
        'callback_data',
        'notes',
        'auto_processed',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'callback_data' => 'json',
            'processed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'auto_processed' => 'boolean',
        ];
    }

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Payment::class, 'transaction_id');
    }

    public function lottery()
    {
        return $this->belongsTo(Lottery::class, 'lottery_id');
    }

    public function order()
    {
        return $this->hasOneThrough(
            Order::class,
            Payment::class,
            'id', // Foreign key on payments table
            'id', // Foreign key on orders table
            'transaction_id', // Local key on refunds table
            'order_id' // Local key on payments table
        );
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeAutoProcessed($query)
    {
        return $query->where('auto_processed', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }

    /**
     * Accessors
     */
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsApprovedAttribute()
    {
        return $this->status === 'approved';
    }

    public function getIsProcessedAttribute()
    {
        return $this->status === 'processed';
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsRejectedAttribute()
    {
        return $this->status === 'rejected';
    }

    /**
     * Business Logic
     */
    public function approve(User $approver = null, string $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver?->id,
            'notes' => $notes ? ($this->notes ? $this->notes . "\n" . $notes : $notes) : $this->notes,
        ]);

        return $this;
    }

    public function reject(User $rejector = null, string $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $rejector?->id,
            'rejection_reason' => $reason,
        ]);

        return $this;
    }

    public function process(User $processor = null, array $callbackData = [])
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
            'processed_by' => $processor?->id,
            'callback_data' => $callbackData,
        ]);

        return $this;
    }

    public function complete(array $callbackData = [])
    {
        $this->update([
            'status' => 'completed',
            'callback_data' => array_merge($this->callback_data ?? [], $callbackData),
        ]);

        return $this;
    }

    public function generateRefundNumber()
    {
        return 'REF-' . date('Y') . '-' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Static factory methods
     */
    public static function createAutomaticRefund(Payment $transaction, string $reason, string $type = 'automatic')
    {
        $refund = self::create([
            'refund_number' => 'REF-' . time() . '-' . $transaction->id,
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
            'lottery_id' => $transaction->lottery_id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'reason' => $reason,
            'type' => $type,
            'status' => 'approved', // Auto-approve automatic refunds
            'approved_at' => now(),
            'auto_processed' => true,
            'refund_method' => config('refund.methods.default', 'mobile_money'), // Default refund method
        ]);

        // Update refund number with proper ID
        $refund->update(['refund_number' => $refund->generateRefundNumber()]);

        return $refund;
    }

    public static function createManualRefund(Payment $transaction, string $reason, User $requestedBy = null)
    {
        $refund = self::create([
            'refund_number' => 'REF-' . time() . '-' . $transaction->id,
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
            'lottery_id' => $transaction->lottery_id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'reason' => $reason,
            'type' => 'manual',
            'status' => 'pending', // Manual refunds need approval
            'auto_processed' => false,
            'refund_method' => config('refund.methods.default', 'mobile_money'),
        ]);

        // Update refund number with proper ID
        $refund->update(['refund_number' => $refund->generateRefundNumber()]);

        return $refund;
    }
}