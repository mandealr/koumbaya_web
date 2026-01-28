<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantRatingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'old_score',
        'new_score',
        'score_change',
        'change_reason',
        'related_entity_type',
        'related_entity_id',
        'calculated_by',
        'details',
    ];

    protected $casts = [
        'old_score' => 'float',
        'new_score' => 'float',
        'score_change' => 'float',
        'details' => 'array',
    ];

    /**
     * Raisons de changement possibles
     */
    public const REASONS = [
        'new_review' => 'Nouvel avis client',
        'order_fulfilled' => 'Commande livrée',
        'order_cancelled' => 'Commande annulée',
        'refund_processed' => 'Remboursement traité',
        'dispute_opened' => 'Litige ouvert',
        'dispute_resolved' => 'Litige résolu',
        'product_created' => 'Nouveau produit créé',
        'lottery_completed' => 'Tombola terminée',
        'admin_action' => 'Action administrateur',
        'manual_recalc' => 'Recalcul manuel',
        'scheduled_recalc' => 'Recalcul planifié',
        'initial_calc' => 'Calcul initial',
    ];

    // ==================== RELATIONS ====================

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    // ==================== SCOPES ====================

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByReason($query, string $reason)
    {
        return $query->where('change_reason', $reason);
    }

    public function scopeSignificantChanges($query, float $threshold = 5.0)
    {
        return $query->where(function ($q) use ($threshold) {
            $q->where('score_change', '>=', $threshold)
              ->orWhere('score_change', '<=', -$threshold);
        });
    }

    public function scopeForMerchant($query, int $merchantId)
    {
        return $query->where('merchant_id', $merchantId)->orderBy('created_at', 'desc');
    }

    // ==================== ACCESSORS ====================

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->change_reason] ?? $this->change_reason;
    }

    public function getTrendAttribute(): string
    {
        if ($this->score_change > 0) return 'up';
        if ($this->score_change < 0) return 'down';
        return 'stable';
    }

    public function getTrendIconAttribute(): string
    {
        return match ($this->trend) {
            'up' => '📈',
            'down' => '📉',
            default => '➡️',
        };
    }

    // ==================== METHODS ====================

    /**
     * Crée un log de changement
     */
    public static function logChange(
        int $merchantId,
        ?float $oldScore,
        float $newScore,
        string $reason,
        ?string $entityType = null,
        ?int $entityId = null,
        string $calculatedBy = 'system',
        ?array $details = null
    ): self {
        return self::create([
            'merchant_id' => $merchantId,
            'old_score' => $oldScore,
            'new_score' => $newScore,
            'score_change' => $oldScore !== null ? round($newScore - $oldScore, 2) : 0,
            'change_reason' => $reason,
            'related_entity_type' => $entityType,
            'related_entity_id' => $entityId,
            'calculated_by' => $calculatedBy,
            'details' => $details,
        ]);
    }

    /**
     * Retourne une description lisible du changement
     */
    public function describe(): string
    {
        $change = $this->score_change >= 0 ? "+{$this->score_change}" : $this->score_change;
        return sprintf(
            '%s %s : Score %s → %s (%s)',
            $this->trend_icon,
            $this->reason_label,
            round($this->old_score ?? 0, 1),
            round($this->new_score, 1),
            $change
        );
    }

    /**
     * Retourne les données pour l'API
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'old_score' => $this->old_score ? round($this->old_score, 1) : null,
            'new_score' => round($this->new_score, 1),
            'change' => round($this->score_change, 1),
            'trend' => $this->trend,
            'reason' => $this->change_reason,
            'reason_label' => $this->reason_label,
            'calculated_by' => $this->calculated_by,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
