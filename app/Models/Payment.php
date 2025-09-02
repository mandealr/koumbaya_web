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
        'order_id',
        'lottery_id',
        'product_id',
        'ebilling_id',
        'payment_method',
        'amount',
        'status',
        'callback_data',
        'paid_at',
        'user_id',
        'transaction_id',
        'currency',
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
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function lottery()
    {
        return $this->belongsTo(Lottery::class, 'lottery_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'payment_id');
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
        // payment_gateway est maintenant dans meta
        return $query->whereJsonContains('meta->payment_gateway', $gateway);
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
     * Accesseurs pour les champs migrés vers meta
     */

    public function getCurrencyAttribute()
    {
        return $this->meta['currency'] ?? 'XAF';
    }

    public function getCustomerNameAttribute()
    {
        if (isset($this->meta['customer_name'])) {
            return $this->meta['customer_name'];
        }
        
        if ($this->order && $this->order->user) {
            $firstName = $this->order->user->first_name ?? '';
            $lastName = $this->order->user->last_name ?? '';
            return trim($firstName . ' ' . $lastName) ?: null;
        }
        
        return null;
    }

    public function getCustomerPhoneAttribute()
    {
        return $this->meta['customer_phone'] ?? $this->order?->user?->phone;
    }

    public function getCustomerEmailAttribute()
    {
        return $this->meta['customer_email'] ?? $this->order?->user?->email;
    }

    public function getDescriptionAttribute()
    {
        return $this->meta['description'] ?? "Paiement pour commande {$this->order?->order_number}";
    }

    public function getPaymentGatewayAttribute()
    {
        return $this->meta['payment_gateway'] ?? 'ebilling';
    }

    public function getGatewayConfigAttribute()
    {
        return $this->meta['gateway_config'] ?? [];
    }

    public function getSuccessUrlAttribute()
    {
        return $this->meta['success_url'] ?? null;
    }

    public function getCallbackUrlAttribute()
    {
        return $this->meta['callback_url'] ?? null;
    }

    public function getGatewayResponseAttribute()
    {
        return $this->meta['gateway_response'] ?? [];
    }

    public function getProcessedAtAttribute()
    {
        return $this->meta['processed_at'] ?? null;
    }

    /**
     * Business Logic pour E-Billing
     */
    public function generateReference()
    {
        return 'KMB-PAY-' . date('YmdHis') . '-' . str_pad($this->id ?? rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function markAsPaid($paymentMethod = null, $transactionId = null, $gatewayResponse = null)
    {
        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'gateway_response' => $gatewayResponse,
            'paid_at' => now(),
        ]);
    }

    public function markAsProcessed()
    {
        $meta = $this->meta ?? [];
        $meta['processed_at'] = now()->toISOString();
        
        $this->update([
            'status' => 'processed',
            'meta' => $meta,
        ]);
    }

    public function markAsCompleted($gatewayResponse = null)
    {
        $meta = $this->meta ?? [];
        
        if ($gatewayResponse) {
            $meta['gateway_response'] = $gatewayResponse;
        }
        
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'meta' => $meta,
        ]);

        // Mettre à jour la commande associée
        if ($this->order) {
            $this->order->markAsPaid($this->reference);
            
            // Mettre à jour les tickets de tombola si nécessaire
            if ($this->order->type === 'lottery' && $this->order->lottery_id) {
                \App\Models\LotteryTicket::where('payment_id', $this->id)
                    ->where('status', 'reserved')
                    ->update([
                        'status' => 'paid',
                        'purchased_at' => now()
                    ]);
            }
        }
    }

    public function markAsFailed($reason = null, $gatewayResponse = null)
    {
        $meta = $this->meta ?? [];
        $meta['gateway_response'] = $gatewayResponse ?? [];
        if ($reason) {
            $meta['gateway_response']['failure_reason'] = $reason;
        }
        
        $this->update([
            'status' => 'failed',
            'meta' => $meta,
        ]);

        // Mettre à jour la commande associée
        if ($this->order) {
            $this->order->update([
                'status' => 'failed',
                'notes' => $reason ?: 'Paiement échoué'
            ]);

            // Annuler les tickets réservés si nécessaire
            if ($this->order->type === 'lottery' && $this->order->lottery_id) {
                \App\Models\LotteryTicket::where('payment_id', $this->id)
                    ->where('status', 'reserved')
                    ->update(['status' => 'cancelled']);
            }
        }
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
