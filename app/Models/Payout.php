<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_reference',
        'payment_system_name',
        'payee_msisdn',
        'amount',
        'payout_type',
        'payout_id',
        'transaction_id',
        'message',
        'status',
        'user_id',
        'transfert_id',
        'merchant_payout_request_id',
        'payout_category',
        'initiated_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transfert()
    {
        return $this->belongsTo(Transfert::class);
    }

    public function merchantPayoutRequest()
    {
        return $this->belongsTo(MerchantPayoutRequest::class);
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', STATUT_DO);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', STATUT_FAILED);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('payout_category', $category);
    }

    public function scopeMerchantRefunds($query)
    {
        return $query->where('payout_category', 'merchant_refund');
    }

    public function scopeSystemRefunds($query)
    {
        return $query->where('payout_category', 'system_refund');
    }

    public function scopeTransfers($query)
    {
        return $query->where('payout_category', 'transfer');
    }

    /**
     * Accesseurs
     */
    public function getIsCompletedAttribute()
    {
        return $this->status == STATUT_DO;
    }

    public function getIsFailedAttribute()
    {
        return $this->status == STATUT_FAILED;
    }

    /**
     * Obtenir le label du type de payout
     */
    public function getCategoryLabel()
    {
        $labels = [
            'transfer' => 'Transfert',
            'merchant_refund' => 'Remboursement marchand',
            'system_refund' => 'Remboursement système'
        ];

        return $labels[$this->payout_category] ?? $this->payout_category;
    }

    /**
     * Obtenir le label de l'opérateur
     */
    public function getOperatorLabel()
    {
        $labels = [
            'airtelmoney' => 'Airtel Money',
            'moovmoney4' => 'Moov Money'
        ];

        return $labels[$this->payment_system_name] ?? $this->payment_system_name;
    }
}