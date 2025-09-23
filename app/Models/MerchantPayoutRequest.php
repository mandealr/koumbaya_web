<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantPayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number', 
        'merchant_id', 
        'order_id', 
        'lottery_id', 
        'product_id',
        'refund_type', 
        'reason', 
        'refund_amount', 
        'customer_id', 
        'customer_phone',
        'payment_operator', 
        'status', 
        'approved_by', 
        'approved_at', 
        'admin_notes',
        'rejection_reason', 
        'payout_id'
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function merchant() 
    { 
        return $this->belongsTo(User::class, 'merchant_id'); 
    }
    
    public function customer() 
    { 
        return $this->belongsTo(User::class, 'customer_id'); 
    }
    
    public function order() 
    { 
        return $this->belongsTo(Order::class); 
    }
    
    public function lottery() 
    { 
        return $this->belongsTo(Lottery::class); 
    }
    
    public function product() 
    { 
        return $this->belongsTo(Product::class); 
    }
    
    public function approver() 
    { 
        return $this->belongsTo(User::class, 'approved_by'); 
    }
    
    public function payout() 
    { 
        return $this->belongsTo(Payout::class); 
    }

    /**
     * Scopes
     */
    public function scopePending($query) 
    {
        return $query->where('status', 'pending');
    }

    public function scopeByMerchant($query, $merchantId) 
    {
        return $query->where('merchant_id', $merchantId);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Générer un numéro de demande unique
     */
    public function generateRequestNumber()
    {
        return 'MPR-' . date('Ymd') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Accesseurs
     */
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsApprovedAttribute()
    {
        return $this->status === 'approved';
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
     * Obtenir le label du type de remboursement
     */
    public function getRefundTypeLabel()
    {
        $labels = [
            'order_cancellation' => 'Annulation de commande',
            'lottery_cancellation' => 'Annulation de tombola',
            'product_defect' => 'Produit défectueux',
            'customer_request' => 'Demande client',
            'other' => 'Autre'
        ];

        return $labels[$this->refund_type] ?? $this->refund_type;
    }

    /**
     * Obtenir le label du statut
     */
    public function getStatusLabel()
    {
        $labels = [
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'processing' => 'En cours de traitement',
            'completed' => 'Terminé',
            'failed' => 'Échoué'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Formater le numéro de téléphone selon l'opérateur
     */
    public function formatPhoneNumber()
    {
        $phone = $this->customer_phone;
        
        // Retirer les espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ajouter le préfixe du Gabon si nécessaire
        if (strlen($phone) == 8) {
            $phone = '241' . $phone;
        }
        
        return $phone;
    }
}