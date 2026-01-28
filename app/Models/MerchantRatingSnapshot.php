<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantRatingSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'overall_score',
        'activity_score',
        'quality_score',
        'reliability_score',
        'avg_rating',
        'total_reviews',
        'completed_sales',
        'fulfilled_orders',
        'refund_rate',
        'dispute_count',
        'snapshot_month',
    ];

    protected $casts = [
        'overall_score' => 'float',
        'activity_score' => 'float',
        'quality_score' => 'float',
        'reliability_score' => 'float',
        'avg_rating' => 'float',
        'refund_rate' => 'float',
        'snapshot_month' => 'date',
    ];

    // ==================== RELATIONS ====================

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function rating(): BelongsTo
    {
        return $this->belongsTo(MerchantRating::class, 'merchant_id', 'merchant_id');
    }

    // ==================== SCOPES ====================

    public function scopeByMonth($query, int $year, int $month)
    {
        $date = sprintf('%04d-%02d-01', $year, $month);
        return $query->where('snapshot_month', $date);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->whereYear('snapshot_month', $year);
    }

    public function scopeLastMonths($query, int $months = 12)
    {
        return $query->where('snapshot_month', '>=', now()->subMonths($months)->startOfMonth());
    }

    public function scopeForMerchant($query, int $merchantId)
    {
        return $query->where('merchant_id', $merchantId)->orderBy('snapshot_month', 'desc');
    }

    // ==================== METHODS ====================

    /**
     * Compare avec un autre snapshot
     */
    public function compareWith(MerchantRatingSnapshot $other): array
    {
        return [
            'overall_score' => [
                'current' => $this->overall_score,
                'previous' => $other->overall_score,
                'change' => round($this->overall_score - $other->overall_score, 2),
                'trend' => $this->overall_score > $other->overall_score ? 'up' : ($this->overall_score < $other->overall_score ? 'down' : 'stable'),
            ],
            'avg_rating' => [
                'current' => $this->avg_rating,
                'previous' => $other->avg_rating,
                'change' => round($this->avg_rating - $other->avg_rating, 2),
            ],
            'total_reviews' => [
                'current' => $this->total_reviews,
                'previous' => $other->total_reviews,
                'change' => $this->total_reviews - $other->total_reviews,
            ],
            'completed_sales' => [
                'current' => $this->completed_sales,
                'previous' => $other->completed_sales,
                'change' => $this->completed_sales - $other->completed_sales,
            ],
        ];
    }

    /**
     * Retourne les données pour un graphique
     */
    public function toChartData(): array
    {
        return [
            'month' => $this->snapshot_month->format('M Y'),
            'overall_score' => round($this->overall_score, 1),
            'activity_score' => round($this->activity_score, 1),
            'quality_score' => round($this->quality_score, 1),
            'reliability_score' => round($this->reliability_score, 1),
            'avg_rating' => round($this->avg_rating, 1),
        ];
    }

    /**
     * Crée un snapshot à partir d'un MerchantRating
     */
    public static function createFromRating(MerchantRating $rating): self
    {
        return self::updateOrCreate(
            [
                'merchant_id' => $rating->merchant_id,
                'snapshot_month' => now()->startOfMonth(),
            ],
            [
                'overall_score' => $rating->overall_score,
                'activity_score' => $rating->activity_score,
                'quality_score' => $rating->quality_score,
                'reliability_score' => $rating->reliability_score,
                'avg_rating' => $rating->avg_rating,
                'total_reviews' => $rating->total_reviews,
                'completed_sales' => $rating->completed_sales,
                'fulfilled_orders' => $rating->fulfilled_orders,
                'refund_rate' => $rating->refund_rate,
                'dispute_count' => $rating->dispute_count,
            ]
        );
    }
}
