<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchantRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'overall_score',
        'activity_score',
        'quality_score',
        'reliability_score',
        'total_products',
        'active_products',
        'completed_sales',
        'fulfilled_orders',
        'total_orders',
        'cancelled_orders',
        'avg_rating',
        'total_reviews',
        'verified_reviews',
        'positive_reviews',
        'neutral_reviews',
        'negative_reviews',
        'total_refunds',
        'refund_rate',
        'dispute_count',
        'dispute_rate',
        'admin_warnings',
        'score_trend',
        'badge',
        'last_recalculated_at',
    ];

    protected $casts = [
        'overall_score' => 'float',
        'activity_score' => 'float',
        'quality_score' => 'float',
        'reliability_score' => 'float',
        'avg_rating' => 'float',
        'refund_rate' => 'float',
        'dispute_rate' => 'float',
        'last_recalculated_at' => 'datetime',
    ];

    /**
     * Badges disponibles avec leurs seuils
     */
    public const BADGES = [
        'excellent' => ['min' => 90, 'label' => 'Excellent', 'stars' => 5, 'color' => 'green'],
        'very_good' => ['min' => 75, 'label' => 'Très bon', 'stars' => 4, 'color' => 'blue'],
        'good' => ['min' => 60, 'label' => 'Bon', 'stars' => 3, 'color' => 'yellow'],
        'average' => ['min' => 40, 'label' => 'Moyen', 'stars' => 2, 'color' => 'orange'],
        'poor' => ['min' => 0, 'label' => 'Faible', 'stars' => 1, 'color' => 'red'],
    ];

    // ==================== RELATIONS ====================

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(MerchantRatingSnapshot::class, 'merchant_id', 'merchant_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MerchantRatingLog::class, 'merchant_id', 'merchant_id');
    }

    // ==================== SCOPES ====================

    public function scopeTopRated($query, int $limit = 10)
    {
        return $query->orderBy('overall_score', 'desc')->limit($limit);
    }

    public function scopeByBadge($query, string $badge)
    {
        return $query->where('badge', $badge);
    }

    public function scopeByScoreRange($query, float $min, float $max)
    {
        return $query->whereBetween('overall_score', [$min, $max]);
    }

    public function scopeNeedsRecalculation($query, int $hoursOld = 24)
    {
        return $query->where(function ($q) use ($hoursOld) {
            $q->whereNull('last_recalculated_at')
              ->orWhere('last_recalculated_at', '<', now()->subHours($hoursOld));
        });
    }

    // ==================== ACCESSORS ====================

    /**
     * Retourne le badge formaté pour l'affichage
     */
    public function getBadgeInfoAttribute(): array
    {
        return self::BADGES[$this->badge] ?? self::BADGES['average'];
    }

    /**
     * Retourne les étoiles sous forme de chaîne
     */
    public function getStarsDisplayAttribute(): string
    {
        $stars = $this->badge_info['stars'];
        return str_repeat('⭐', $stars);
    }

    /**
     * Retourne la note moyenne formatée (ex: "4.5/5")
     */
    public function getRatingDisplayAttribute(): string
    {
        if ($this->total_reviews === 0) {
            return 'Aucun avis';
        }
        return number_format($this->avg_rating, 1) . '/5';
    }

    /**
     * Retourne le score formaté (ex: "85/100")
     */
    public function getScoreDisplayAttribute(): string
    {
        return round($this->overall_score) . '/100';
    }

    // ==================== METHODS ====================

    /**
     * Détermine le badge basé sur le score
     */
    public static function determineBadge(float $score): string
    {
        foreach (self::BADGES as $badge => $config) {
            if ($score >= $config['min']) {
                return $badge;
            }
        }
        return 'poor';
    }

    /**
     * Vérifie si le score nécessite un recalcul
     */
    public function shouldRecalculate(int $hoursThreshold = 24): bool
    {
        if (!$this->last_recalculated_at) {
            return true;
        }
        return $this->last_recalculated_at->diffInHours(now()) >= $hoursThreshold;
    }

    /**
     * Retourne un résumé pour l'API
     */
    public function toSummary(): array
    {
        return [
            'overall_score' => round($this->overall_score, 1),
            'badge' => $this->badge,
            'badge_label' => $this->badge_info['label'],
            'stars' => $this->badge_info['stars'],
            'avg_rating' => round($this->avg_rating, 1),
            'total_reviews' => $this->total_reviews,
            'completed_sales' => $this->completed_sales,
            'score_trend' => $this->score_trend,
        ];
    }

    /**
     * Retourne les détails complets pour l'API
     */
    public function toDetailedArray(): array
    {
        return [
            'scores' => [
                'overall' => round($this->overall_score, 1),
                'activity' => round($this->activity_score, 1),
                'quality' => round($this->quality_score, 1),
                'reliability' => round($this->reliability_score, 1),
            ],
            'badge' => [
                'key' => $this->badge,
                'label' => $this->badge_info['label'],
                'stars' => $this->badge_info['stars'],
                'color' => $this->badge_info['color'],
            ],
            'activity' => [
                'total_products' => $this->total_products,
                'active_products' => $this->active_products,
                'completed_sales' => $this->completed_sales,
                'fulfilled_orders' => $this->fulfilled_orders,
                'total_orders' => $this->total_orders,
            ],
            'reviews' => [
                'avg_rating' => round($this->avg_rating, 1),
                'total' => $this->total_reviews,
                'verified' => $this->verified_reviews,
                'positive' => $this->positive_reviews,
                'neutral' => $this->neutral_reviews,
                'negative' => $this->negative_reviews,
            ],
            'reliability' => [
                'refund_rate' => round($this->refund_rate, 1),
                'dispute_count' => $this->dispute_count,
                'admin_warnings' => $this->admin_warnings,
            ],
            'trend' => $this->score_trend,
            'last_updated' => $this->last_recalculated_at?->toIso8601String(),
        ];
    }
}
