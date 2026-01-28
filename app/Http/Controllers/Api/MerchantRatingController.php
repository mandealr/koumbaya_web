<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MerchantRating;
use App\Models\UserRating;
use App\Services\MerchantRatingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Merchant Ratings",
 *     description="API pour la notation et les avis des marchands"
 * )
 */
class MerchantRatingController extends Controller
{
    protected MerchantRatingService $ratingService;

    public function __construct(MerchantRatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * Récupérer la notation d'un marchand
     *
     * @OA\Get(
     *     path="/api/merchants/{id}/rating",
     *     tags={"Merchant Ratings"},
     *     summary="Récupérer la notation d'un marchand",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Notation du marchand"),
     *     @OA\Response(response=404, description="Marchand non trouvé")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $merchant = User::find($id);

        if (!$merchant || !$merchant->isMerchant()) {
            return response()->json([
                'success' => false,
                'error' => 'Marchand non trouvé'
            ], 404);
        }

        $rating = $this->ratingService->getMerchantRating($id);

        if (!$rating) {
            // Calculer si pas encore de rating
            try {
                $rating = $this->ratingService->calculateAndUpdateScore($merchant, 'initial_calc');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => true,
                    'data' => $this->getDefaultRating($merchant)
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'merchant' => [
                    'id' => $merchant->id,
                    'name' => $merchant->business_name ?: ($merchant->first_name . ' ' . $merchant->last_name),
                    'avatar' => $merchant->avatar,
                ],
                'rating' => $rating->toDetailedArray(),
            ]
        ]);
    }

    /**
     * Récupérer le résumé de la notation d'un marchand
     *
     * @OA\Get(
     *     path="/api/merchants/{id}/rating/summary",
     *     tags={"Merchant Ratings"},
     *     summary="Récupérer le résumé de la notation",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Résumé de la notation")
     * )
     */
    public function summary(int $id): JsonResponse
    {
        $rating = $this->ratingService->getMerchantRating($id);

        if (!$rating) {
            $merchant = User::find($id);
            if (!$merchant || !$merchant->isMerchant()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Marchand non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'overall_score' => 50,
                    'badge' => 'average',
                    'badge_label' => 'Nouveau',
                    'stars' => 3,
                    'avg_rating' => 0,
                    'total_reviews' => 0,
                    'is_new' => true,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $rating->toSummary()
        ]);
    }

    /**
     * Récupérer l'historique des scores d'un marchand
     *
     * @OA\Get(
     *     path="/api/merchants/{id}/rating/history",
     *     tags={"Merchant Ratings"},
     *     summary="Récupérer l'historique des scores",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="months", in="query", @OA\Schema(type="integer", default=12)),
     *     @OA\Response(response=200, description="Historique mensuel")
     * )
     */
    public function history(Request $request, int $id): JsonResponse
    {
        $months = min($request->get('months', 12), 24);
        $history = $this->ratingService->getScoreHistory($id, $months);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Récupérer les avis d'un marchand
     *
     * @OA\Get(
     *     path="/api/merchants/{id}/reviews",
     *     tags={"Merchant Ratings"},
     *     summary="Récupérer les avis clients",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=10)),
     *     @OA\Response(response=200, description="Liste des avis")
     * )
     */
    public function reviews(Request $request, int $id): JsonResponse
    {
        $perPage = min($request->get('per_page', 10), 50);

        $reviews = UserRating::where('rated_user_id', $id)
            ->where('type', 'seller')
            ->with(['rater:id,first_name,last_name,avatar', 'product:id,name,image'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'is_verified' => $review->is_verified,
                    'created_at' => $review->created_at->toIso8601String(),
                    'reviewer' => $review->rater ? [
                        'id' => $review->rater->id,
                        'name' => $review->rater->first_name . ' ' . substr($review->rater->last_name, 0, 1) . '.',
                        'avatar' => $review->rater->avatar,
                    ] : null,
                    'product' => $review->product ? [
                        'id' => $review->product->id,
                        'name' => $review->product->name,
                        'image' => $review->product->image,
                    ] : null,
                ];
            }),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Ajouter un avis sur un marchand
     *
     * @OA\Post(
     *     path="/api/merchants/{id}/reviews",
     *     tags={"Merchant Ratings"},
     *     summary="Ajouter un avis",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(property="order_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Avis créé"),
     *     @OA\Response(response=422, description="Validation échouée")
     * )
     */
    public function storeReview(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'order_id' => 'nullable|exists:orders,id',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $merchant = User::find($id);

        if (!$merchant || !$merchant->isMerchant()) {
            return response()->json([
                'success' => false,
                'error' => 'Marchand non trouvé'
            ], 404);
        }

        // Vérifier que l'utilisateur ne note pas lui-même
        if ($user->id === $id) {
            return response()->json([
                'success' => false,
                'error' => 'Vous ne pouvez pas vous noter vous-même'
            ], 422);
        }

        // Vérifier si déjà noté pour cette commande
        if ($request->order_id) {
            $existingReview = UserRating::where('rater_user_id', $user->id)
                ->where('rated_user_id', $id)
                ->where('order_id', $request->order_id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'error' => 'Vous avez déjà noté ce marchand pour cette commande'
                ], 422);
            }
        }

        // Vérifier si achat vérifié
        $isVerified = false;
        if ($request->order_id) {
            $order = \App\Models\Order::find($request->order_id);
            if ($order && $order->user_id === $user->id && in_array($order->status, ['paid', 'fulfilled'])) {
                $isVerified = true;
            }
        }

        // Créer l'avis
        $review = UserRating::create([
            'rated_user_id' => $id,
            'rater_user_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'order_id' => $request->order_id,
            'product_id' => $request->product_id,
            'type' => 'seller',
            'is_verified' => $isVerified,
        ]);

        // Recalculer le score du marchand
        try {
            $this->ratingService->calculateAndUpdateScore($merchant, 'new_review', 'user_rating', $review->id);
        } catch (\Exception $e) {
            \Log::error('Erreur recalcul score après avis', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Avis ajouté avec succès',
            'data' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'is_verified' => $review->is_verified,
            ]
        ], 201);
    }

    /**
     * Récupérer le classement des meilleurs marchands
     *
     * @OA\Get(
     *     path="/api/merchants/top-rated",
     *     tags={"Merchant Ratings"},
     *     summary="Classement des meilleurs marchands",
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", default=10)),
     *     @OA\Response(response=200, description="Liste des meilleurs marchands")
     * )
     */
    public function topRated(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 50);
        $topMerchants = $this->ratingService->getTopMerchants($limit);

        return response()->json([
            'success' => true,
            'data' => $topMerchants
        ]);
    }

    /**
     * Récupérer la notation du marchand connecté
     *
     * @OA\Get(
     *     path="/api/merchant/my-rating",
     *     tags={"Merchant Ratings"},
     *     summary="Ma notation (marchand connecté)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Notation du marchand")
     * )
     */
    public function myRating(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMerchant()) {
            return response()->json([
                'success' => false,
                'error' => 'Vous n\'êtes pas un marchand'
            ], 403);
        }

        $rating = $this->ratingService->getMerchantRating($user->id);

        if (!$rating) {
            try {
                $rating = $this->ratingService->calculateAndUpdateScore($user, 'initial_calc');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => true,
                    'data' => $this->getDefaultRating($user)
                ]);
            }
        }

        $logs = $this->ratingService->getChangeLogs($user->id, 10);
        $history = $this->ratingService->getScoreHistory($user->id, 6);

        return response()->json([
            'success' => true,
            'data' => [
                'rating' => $rating->toDetailedArray(),
                'recent_changes' => $logs,
                'history' => $history,
            ]
        ]);
    }

    /**
     * Forcer le recalcul de la notation (admin uniquement)
     */
    public function recalculate(int $id): JsonResponse
    {
        $user = Auth::user();

        // Vérifier les droits admin
        if (!$user->hasRole('admin') && !$user->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'error' => 'Non autorisé'
            ], 403);
        }

        $merchant = User::find($id);

        if (!$merchant || !$merchant->isMerchant()) {
            return response()->json([
                'success' => false,
                'error' => 'Marchand non trouvé'
            ], 404);
        }

        try {
            $rating = $this->ratingService->calculateAndUpdateScore($merchant, 'admin_action');

            return response()->json([
                'success' => true,
                'message' => 'Score recalculé avec succès',
                'data' => $rating->toDetailedArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du recalcul : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retourne un rating par défaut pour un nouveau marchand
     */
    private function getDefaultRating(User $merchant): array
    {
        return [
            'merchant' => [
                'id' => $merchant->id,
                'name' => $merchant->business_name ?: ($merchant->first_name . ' ' . $merchant->last_name),
            ],
            'rating' => [
                'scores' => [
                    'overall' => 50,
                    'activity' => 50,
                    'quality' => 50,
                    'reliability' => 50,
                ],
                'badge' => [
                    'key' => 'average',
                    'label' => 'Nouveau',
                    'stars' => 3,
                    'color' => 'gray',
                ],
                'reviews' => [
                    'avg_rating' => 0,
                    'total' => 0,
                ],
                'is_new' => true,
            ]
        ];
    }
}
