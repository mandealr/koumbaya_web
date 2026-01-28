<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\MerchantRating;
use App\Models\MerchantRatingSnapshot;
use App\Models\MerchantRatingLog;
use App\Models\UserRating;
use App\Models\Refund;
use App\Models\RefundRequest;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MerchantRatingService
{
    /**
     * Poids des composantes du score
     */
    private const WEIGHT_ACTIVITY = 0.40;
    private const WEIGHT_QUALITY = 0.40;
    private const WEIGHT_RELIABILITY = 0.20;

    /**
     * Seuils de référence
     */
    private const MAX_EXPECTED_PRODUCTS = 50;
    private const MAX_EXPECTED_SALES = 100;
    private const MAX_REFUND_RATE = 20.0;
    private const MAX_DISPUTE_RATE = 10.0;
    private const MAX_WARNINGS = 3;
    private const MIN_REVIEWS_FOR_QUALITY = 5;

    /**
     * Durée du cache en secondes
     */
    private const CACHE_TTL = 3600; // 1 heure

    /**
     * Calcule et met à jour le score d'un marchand
     */
    public function calculateAndUpdateScore(User $merchant, string $reason = 'manual_recalc', ?string $entityType = null, ?int $entityId = null): MerchantRating
    {
        if (!$merchant->isMerchant()) {
            throw new \InvalidArgumentException("L'utilisateur n'est pas un marchand");
        }

        $merchantId = $merchant->id;

        // Récupérer ou créer le rating
        $rating = MerchantRating::firstOrNew(['merchant_id' => $merchantId]);
        $oldScore = $rating->exists ? $rating->overall_score : null;

        // Calculer les métriques
        $activityMetrics = $this->calculateActivityMetrics($merchantId);
        $qualityMetrics = $this->calculateQualityMetrics($merchantId);
        $reliabilityMetrics = $this->calculateReliabilityMetrics($merchantId);

        // Calculer les scores
        $activityScore = $this->calculateActivityScore($activityMetrics);
        $qualityScore = $this->calculateQualityScore($qualityMetrics);
        $reliabilityScore = $this->calculateReliabilityScore($reliabilityMetrics);

        // Score global
        $overallScore = ($activityScore * self::WEIGHT_ACTIVITY) +
                        ($qualityScore * self::WEIGHT_QUALITY) +
                        ($reliabilityScore * self::WEIGHT_RELIABILITY);

        // Déterminer le badge et la tendance
        $badge = MerchantRating::determineBadge($overallScore);
        $trend = $this->determineTrend($merchantId, $overallScore);

        // Mettre à jour le rating
        $rating->fill([
            'overall_score' => round($overallScore, 2),
            'activity_score' => round($activityScore, 2),
            'quality_score' => round($qualityScore, 2),
            'reliability_score' => round($reliabilityScore, 2),
            // Activité
            'total_products' => $activityMetrics['total_products'],
            'active_products' => $activityMetrics['active_products'],
            'completed_sales' => $activityMetrics['completed_sales'],
            'fulfilled_orders' => $activityMetrics['fulfilled_orders'],
            'total_orders' => $activityMetrics['total_orders'],
            'cancelled_orders' => $activityMetrics['cancelled_orders'],
            // Qualité
            'avg_rating' => round($qualityMetrics['avg_rating'], 2),
            'total_reviews' => $qualityMetrics['total_reviews'],
            'verified_reviews' => $qualityMetrics['verified_reviews'],
            'positive_reviews' => $qualityMetrics['positive_reviews'],
            'neutral_reviews' => $qualityMetrics['neutral_reviews'],
            'negative_reviews' => $qualityMetrics['negative_reviews'],
            // Fiabilité
            'total_refunds' => $reliabilityMetrics['total_refunds'],
            'refund_rate' => round($reliabilityMetrics['refund_rate'], 2),
            'dispute_count' => $reliabilityMetrics['dispute_count'],
            'dispute_rate' => round($reliabilityMetrics['dispute_rate'], 2),
            'admin_warnings' => $reliabilityMetrics['admin_warnings'],
            // Meta
            'badge' => $badge,
            'score_trend' => $trend,
            'last_recalculated_at' => now(),
        ]);

        $rating->save();

        // Mettre à jour les colonnes dénormalisées dans User
        $merchant->update([
            'merchant_score' => round($overallScore, 2),
            'merchant_badge' => $badge,
            'merchant_score_updated_at' => now(),
        ]);

        // Logger le changement
        MerchantRatingLog::logChange(
            $merchantId,
            $oldScore,
            $overallScore,
            $reason,
            $entityType,
            $entityId,
            'system',
            [
                'activity_score' => $activityScore,
                'quality_score' => $qualityScore,
                'reliability_score' => $reliabilityScore,
            ]
        );

        // Invalider le cache
        Cache::forget("merchant_rating_{$merchantId}");

        Log::info('MerchantRating :: Score recalculé', [
            'merchant_id' => $merchantId,
            'old_score' => $oldScore,
            'new_score' => $overallScore,
            'badge' => $badge,
            'reason' => $reason,
        ]);

        return $rating;
    }

    /**
     * Calcule les métriques d'activité
     */
    private function calculateActivityMetrics(int $merchantId): array
    {
        // Produits
        $totalProducts = Product::where('merchant_id', $merchantId)->count();
        $activeProducts = Product::where('merchant_id', $merchantId)
            ->where('status', 'active')
            ->count();

        // Commandes via produits du marchand
        $ordersQuery = Order::whereHas('product', function ($q) use ($merchantId) {
            $q->where('merchant_id', $merchantId);
        });

        // Commandes via tombolas du marchand
        $lotteryOrdersQuery = Order::where('type', 'lottery')
            ->whereHas('lottery.product', function ($q) use ($merchantId) {
                $q->where('merchant_id', $merchantId);
            });

        $totalOrders = (clone $ordersQuery)->count() + (clone $lotteryOrdersQuery)->count();

        $fulfilledOrders = (clone $ordersQuery)->where('status', OrderStatus::FULFILLED->value)->count() +
                          (clone $lotteryOrdersQuery)->where('status', OrderStatus::FULFILLED->value)->count();

        $paidOrders = (clone $ordersQuery)->whereIn('status', [OrderStatus::PAID->value, OrderStatus::FULFILLED->value])->count() +
                     (clone $lotteryOrdersQuery)->whereIn('status', [OrderStatus::PAID->value, OrderStatus::FULFILLED->value])->count();

        $cancelledOrders = (clone $ordersQuery)->where('status', OrderStatus::CANCELLED->value)->count() +
                          (clone $lotteryOrdersQuery)->where('status', OrderStatus::CANCELLED->value)->count();

        // Tombolas complétées
        $completedLotteries = DB::table('lotteries')
            ->join('products', 'lotteries.product_id', '=', 'products.id')
            ->where('products.merchant_id', $merchantId)
            ->where('lotteries.status', 'completed')
            ->count();

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'completed_sales' => $paidOrders + $completedLotteries,
            'fulfilled_orders' => $fulfilledOrders,
            'total_orders' => $totalOrders,
            'cancelled_orders' => $cancelledOrders,
            'completed_lotteries' => $completedLotteries,
        ];
    }

    /**
     * Calcule les métriques de qualité (avis clients)
     */
    private function calculateQualityMetrics(int $merchantId): array
    {
        // Vérifier si la table user_ratings existe
        if (!DB::getSchemaBuilder()->hasTable('user_ratings')) {
            return [
                'avg_rating' => 0,
                'total_reviews' => 0,
                'verified_reviews' => 0,
                'positive_reviews' => 0,
                'neutral_reviews' => 0,
                'negative_reviews' => 0,
            ];
        }

        $reviews = DB::table('user_ratings')
            ->where('rated_user_id', $merchantId)
            ->where('type', 'seller')
            ->get();

        $totalReviews = $reviews->count();
        $avgRating = $totalReviews > 0 ? $reviews->avg('rating') : 0;
        $verifiedReviews = $reviews->where('is_verified', true)->count();
        $positiveReviews = $reviews->where('rating', '>=', 4)->count();
        $neutralReviews = $reviews->where('rating', 3)->count();
        $negativeReviews = $reviews->where('rating', '<=', 2)->count();

        return [
            'avg_rating' => $avgRating,
            'total_reviews' => $totalReviews,
            'verified_reviews' => $verifiedReviews,
            'positive_reviews' => $positiveReviews,
            'neutral_reviews' => $neutralReviews,
            'negative_reviews' => $negativeReviews,
        ];
    }

    /**
     * Calcule les métriques de fiabilité
     */
    private function calculateReliabilityMetrics(int $merchantId): array
    {
        // Remboursements liés aux produits du marchand
        $refundsCount = Refund::whereHas('transaction.order.product', function ($q) use ($merchantId) {
            $q->where('merchant_id', $merchantId);
        })->whereIn('status', ['completed', 'approved'])->count();

        // Litiges (refund requests)
        $disputeCount = 0;
        if (DB::getSchemaBuilder()->hasTable('refund_requests')) {
            $disputeCount = RefundRequest::whereHas('lottery.product', function ($q) use ($merchantId) {
                $q->where('merchant_id', $merchantId);
            })->count();
        }

        // Total des commandes pour calculer les taux
        $totalOrders = Order::whereHas('product', function ($q) use ($merchantId) {
            $q->where('merchant_id', $merchantId);
        })->count();

        $refundRate = $totalOrders > 0 ? ($refundsCount / $totalOrders) * 100 : 0;
        $disputeRate = $totalOrders > 0 ? ($disputeCount / $totalOrders) * 100 : 0;

        // Avertissements admin (à implémenter si table existe)
        $adminWarnings = 0;

        return [
            'total_refunds' => $refundsCount,
            'refund_rate' => $refundRate,
            'dispute_count' => $disputeCount,
            'dispute_rate' => $disputeRate,
            'admin_warnings' => $adminWarnings,
            'total_orders' => $totalOrders,
        ];
    }

    /**
     * Calcule le score d'activité (0-100)
     */
    private function calculateActivityScore(array $metrics): float
    {
        // Produits publiés (10 points)
        $productScore = min(($metrics['active_products'] / self::MAX_EXPECTED_PRODUCTS) * 10, 10);

        // Ventes complétées (15 points)
        $salesScore = min(($metrics['completed_sales'] / self::MAX_EXPECTED_SALES) * 15, 15);

        // Taux de livraison (10 points)
        $fulfillRate = $metrics['total_orders'] > 0
            ? ($metrics['fulfilled_orders'] / $metrics['total_orders'])
            : 0.5;
        $fulfillScore = $fulfillRate * 10;

        // Pénalité annulations (5 points max de pénalité)
        $cancelRate = $metrics['total_orders'] > 0
            ? ($metrics['cancelled_orders'] / $metrics['total_orders'])
            : 0;
        $cancelPenalty = min($cancelRate * 10, 5);

        $rawScore = $productScore + $salesScore + $fulfillScore - $cancelPenalty;
        $maxScore = 35; // 10 + 15 + 10

        return ($rawScore / $maxScore) * 100;
    }

    /**
     * Calcule le score de qualité (0-100)
     */
    private function calculateQualityScore(array $metrics): float
    {
        // Si pas assez d'avis, score neutre
        if ($metrics['total_reviews'] < self::MIN_REVIEWS_FOR_QUALITY) {
            return 50.0;
        }

        // Moyenne des notes (20 points)
        $ratingScore = ($metrics['avg_rating'] / 5) * 20;

        // Taux d'avis vérifiés (10 points)
        $verifiedRate = $metrics['total_reviews'] > 0
            ? ($metrics['verified_reviews'] / $metrics['total_reviews'])
            : 0;
        $verifiedScore = $verifiedRate * 10;

        // Taux d'avis positifs (10 points)
        $positiveRate = $metrics['total_reviews'] > 0
            ? ($metrics['positive_reviews'] / $metrics['total_reviews'])
            : 0;
        $positiveScore = $positiveRate * 10;

        $rawScore = $ratingScore + $verifiedScore + $positiveScore;
        $maxScore = 40;

        return ($rawScore / $maxScore) * 100;
    }

    /**
     * Calcule le score de fiabilité (0-100)
     */
    private function calculateReliabilityScore(array $metrics): float
    {
        // Score remboursements (10 points)
        $refundScore = max(0, (1 - ($metrics['refund_rate'] / self::MAX_REFUND_RATE)) * 10);

        // Score litiges (7 points)
        $disputeScore = max(0, (1 - ($metrics['dispute_rate'] / self::MAX_DISPUTE_RATE)) * 7);

        // Score avertissements (3 points)
        $warningScore = max(0, (1 - ($metrics['admin_warnings'] / self::MAX_WARNINGS)) * 3);

        $rawScore = $refundScore + $disputeScore + $warningScore;
        $maxScore = 20;

        return ($rawScore / $maxScore) * 100;
    }

    /**
     * Détermine la tendance du score
     */
    private function determineTrend(int $merchantId, float $currentScore): string
    {
        $lastSnapshot = MerchantRatingSnapshot::where('merchant_id', $merchantId)
            ->orderBy('snapshot_month', 'desc')
            ->first();

        if (!$lastSnapshot) {
            return 'stable';
        }

        $diff = $currentScore - $lastSnapshot->overall_score;

        if ($diff >= 2) return 'up';
        if ($diff <= -2) return 'down';
        return 'stable';
    }

    /**
     * Récupère le rating d'un marchand (avec cache)
     */
    public function getMerchantRating(int $merchantId): ?MerchantRating
    {
        return Cache::remember("merchant_rating_{$merchantId}", self::CACHE_TTL, function () use ($merchantId) {
            return MerchantRating::where('merchant_id', $merchantId)->first();
        });
    }

    /**
     * Récupère l'historique des scores
     */
    public function getScoreHistory(int $merchantId, int $months = 12): array
    {
        return MerchantRatingSnapshot::forMerchant($merchantId)
            ->lastMonths($months)
            ->get()
            ->map(fn($s) => $s->toChartData())
            ->toArray();
    }

    /**
     * Récupère les logs de changement
     */
    public function getChangeLogs(int $merchantId, int $limit = 20): array
    {
        return MerchantRatingLog::forMerchant($merchantId)
            ->limit($limit)
            ->get()
            ->map(fn($l) => $l->toApiArray())
            ->toArray();
    }

    /**
     * Recalcule tous les scores des marchands
     */
    public function recalculateAllScores(): array
    {
        $merchants = User::where(function ($q) {
            $q->whereHas('roles', function ($rq) {
                $rq->whereIn('name', ['business_individual', 'business_enterprise', 'Business Individual', 'Business Enterprise']);
            });
        })->get();

        $results = [
            'total' => $merchants->count(),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($merchants as $merchant) {
            try {
                $this->calculateAndUpdateScore($merchant, 'scheduled_recalc');
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'merchant_id' => $merchant->id,
                    'error' => $e->getMessage(),
                ];
                Log::error('MerchantRating :: Erreur recalcul', [
                    'merchant_id' => $merchant->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Crée un snapshot mensuel pour tous les marchands
     */
    public function createMonthlySnapshots(): int
    {
        $ratings = MerchantRating::all();
        $count = 0;

        foreach ($ratings as $rating) {
            MerchantRatingSnapshot::createFromRating($rating);
            $count++;
        }

        return $count;
    }

    /**
     * Récupère le classement des meilleurs marchands
     */
    public function getTopMerchants(int $limit = 10): array
    {
        return MerchantRating::with('merchant:id,first_name,last_name,business_name,avatar')
            ->topRated($limit)
            ->get()
            ->map(function ($rating) {
                return [
                    'merchant' => [
                        'id' => $rating->merchant->id,
                        'name' => $rating->merchant->business_name ?: ($rating->merchant->first_name . ' ' . $rating->merchant->last_name),
                        'avatar' => $rating->merchant->avatar,
                    ],
                    'rating' => $rating->toSummary(),
                ];
            })
            ->toArray();
    }
}
