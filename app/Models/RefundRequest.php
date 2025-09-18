<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefundRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'lottery_id',
        'amount',
        'reason',
        'reason_type',
        'status',
        'requested_by',
        'approved_by',
        'processed_by',
        'tickets_count',
        'phone_number',
        'refund_method',
        'transaction_id',
        'metadata',
        'admin_notes',
        'processed_at',
        'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    /**
     * Relationship with the user who requested the refund
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with the lottery
     */
    public function lottery()
    {
        return $this->belongsTo(Lottery::class);
    }

    /**
     * Relationship with the user who requested the refund (admin)
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Relationship with the admin who approved the refund
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship with the admin who processed the refund
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope for pending refunds
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved refunds
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for completed refunds
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for rejected refunds
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'completed' => 'Terminé',
            'rejected' => 'Rejeté',
            'processing' => 'En cours',
            default => $this->status
        };
    }

    /**
     * Get the reason type label
     */
    public function getReasonTypeLabelAttribute()
    {
        return match($this->reason_type) {
            'lottery_cancellation' => 'Annulation de tombola',
            'technical_issue' => 'Problème technique',
            'insufficient_participation' => 'Participation insuffisante',
            'merchant_request' => 'Demande du marchand',
            'product_unavailable' => 'Produit non disponible',
            'customer_request' => 'Demande du client',
            'admin_decision' => 'Décision administrative',
            'other' => 'Autre',
            default => $this->reason_type
        };
    }

    /**
     * Get the refund method label
     */
    public function getRefundMethodLabelAttribute()
    {
        return match($this->refund_method) {
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Virement bancaire',
            'cash' => 'Espèces',
            'voucher' => 'Bon d\'achat',
            default => $this->refund_method
        };
    }

    /**
     * Check if the refund can be approved
     */
    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the refund can be processed
     */
    public function canBeProcessed()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the refund can be rejected
     */
    public function canBeRejected()
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    /**
     * Approve the refund
     */
    public function approve($adminId, $notes = null)
    {
        if (!$this->canBeApproved()) {
            throw new \Exception('Cette demande de remboursement ne peut pas être approuvée');
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_notes' => $notes
        ]);

        return $this;
    }

    /**
     * Process the refund (mark as completed)
     */
    public function process($adminId, $transactionId = null, $notes = null)
    {
        if (!$this->canBeProcessed()) {
            throw new \Exception('Cette demande de remboursement ne peut pas être traitée');
        }

        $this->update([
            'status' => 'completed',
            'processed_by' => $adminId,
            'processed_at' => now(),
            'transaction_id' => $transactionId,
            'admin_notes' => $notes ? ($this->admin_notes . "\n" . $notes) : $this->admin_notes
        ]);

        return $this;
    }

    /**
     * Reject the refund
     */
    public function reject($adminId, $reason)
    {
        if (!$this->canBeRejected()) {
            throw new \Exception('Cette demande de remboursement ne peut pas être rejetée');
        }

        $this->update([
            'status' => 'rejected',
            'processed_by' => $adminId,
            'processed_at' => now(),
            'admin_notes' => $reason
        ]);

        return $this;
    }
}