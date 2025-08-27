<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\PaymentStatus;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'order_id',
        'amount',
        'currency',
        'customer_name',
        'customer_phone',
        'customer_email',
        'description',
        'payment_gateway',
        'gateway_config',
        'ebilling_id',
        'success_url',
        'callback_url',
        'payment_method',
        'external_transaction_id',
        'gateway_response',
        'callback_data',
        'status',
        'paid_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_config' => 'array',
            'gateway_response' => 'array',
            'callback_data' => 'array',
            'paid_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Relations
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway', 'name');
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
        return $query->where('status', 'pending');
    }

    public function scopeByGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Accessors
     */
    public function getIsPaidAttribute()
    {
        return $this->status === PaymentStatus::PAID->value;
    }

    public function getIsPendingAttribute()
    {
        return $this->status === PaymentStatus::PENDING->value;
    }

    public function getIsFailedAttribute()
    {
        return in_array($this->status, [PaymentStatus::FAILED->value, PaymentStatus::EXPIRED->value]);
    }

    /**
     * Get payment status as enum
     */
    public function getStatusEnumAttribute(): PaymentStatus
    {
        return PaymentStatus::from($this->status);
    }

    /**
     * Business Logic pour E-Billing
     */
    public function generateReference()
    {
        return 'KMB-PAY-' . date('YmdHis') . '-' . str_pad($this->id ?? rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function markAsPaid($paymentMethod = null, $externalTransactionId = null, $gatewayResponse = null)
    {
        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'external_transaction_id' => $externalTransactionId,
            'gateway_response' => $gatewayResponse,
            'paid_at' => now(),
        ]);
    }

    public function markAsProcessed()
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed($reason = null)
    {
        $gatewayResponse = $this->gateway_response ?: [];
        $gatewayResponse['failure_reason'] = $reason;
        
        $this->update([
            'status' => 'failed',
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Méthodes spécifiques E-Billing
     */
    public function getEBillingPayload()
    {
        return [
            'amount' => $this->amount,
            'reference' => $this->reference,
            'description' => $this->description,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'success_url' => $this->success_url,
            'callback_url' => $this->callback_url,
        ];
    }
}
