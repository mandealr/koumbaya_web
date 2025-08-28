<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Public Results",
 *     description="API endpoints publiques pour les résultats des tombolas"
 * )
 */
class PublicResultsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/public/results",
     *     tags={"Public Results"},
     *     summary="Liste des résultats de tombolas",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de résultats",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Période (week, month, year)",
     *         @OA\Schema(type="string", default="month")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des résultats",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="results", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="stats", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $limit = min($request->get('limit', 20), 50);
        $period = $request->get('period', 'month');

        // Définir la période
        $startDate = match($period) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth()
        };

        // Récupérer les tombolas terminées avec gagnants
        $results = Lottery::with(['product.category', 'winner', 'tickets' => function($query) {
            $query->where('is_winner', true);
        }])
        ->where('status', 'completed')
        ->whereNotNull('winner_user_id')
        ->where('draw_date', '>=', $startDate)
        ->orderBy('draw_date', 'desc')
        ->limit($limit)
        ->get();

        // Statistiques générales
        $stats = $this->getPublicStats($startDate);

        return $this->sendResponse([
            'results' => $results->map(function ($lottery) {
                return [
                    'lottery_id' => $lottery->id,
                    'lottery_number' => $lottery->lottery_number,
                    'product' => [
                        'title' => $lottery->product->title,
                        'image_url' => $lottery->product->image_url,
                        'price' => $lottery->product->price,
                        'category' => $lottery->product->category->name,
                    ],
                    'winner' => [
                        'name' => $lottery->winner ? substr($lottery->winner->first_name, 0, 1) . '****' : 'Anonyme',
                        'initial' => $lottery->winner ? substr($lottery->winner->first_name, 0, 1) : '?',
                        'city' => $lottery->winner->city ?? 'Non spécifiée',
                    ],
                    'winning_ticket' => $lottery->tickets->where('is_winner', true)->first()?->ticket_number,
                    'total_tickets' => $lottery->total_tickets, // Uses accessor for API compatibility
                    'sold_tickets' => $lottery->sold_tickets,
                    'participation_rate' => $lottery->sold_tickets > 0 ? round(($lottery->sold_tickets / $lottery->max_tickets) * 100, 2) : 0,
                    'draw_date' => $lottery->draw_date,
                    'total_revenue' => $lottery->sold_tickets * $lottery->ticket_price,
                    'verification_code' => $this->generateVerificationCode($lottery),
                ];
            }),
            'stats' => $stats,
            'period' => $period
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/public/results/{lottery}",
     *     tags={"Public Results"},
     *     summary="Détail d'un résultat de tombola",
     *     @OA\Parameter(
     *         name="lottery",
     *         in="path",
     *         description="ID ou numéro de la tombola",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Détail du résultat")
     * )
     */
    public function show($lottery)
    {
        // Chercher par ID ou numéro de tombola
        $lotteryRecord = Lottery::with(['product.category', 'winner', 'tickets' => function($query) {
            $query->where('is_winner', true);
        }])
        ->where(function($query) use ($lottery) {
            $query->where('id', $lottery)
                  ->orWhere('lottery_number', $lottery);
        })
        ->where('status', 'completed')
        ->whereNotNull('winner_user_id')
        ->first();

        if (!$lotteryRecord) {
            return $this->sendError('Résultat de tombola non trouvé ou non disponible publiquement.');
        }

        $winningTicket = $lotteryRecord->tickets->where('is_winner', true)->first();

        return $this->sendResponse([
            'lottery_id' => $lotteryRecord->id,
            'lottery_number' => $lotteryRecord->lottery_number,
            'product' => [
                'title' => $lotteryRecord->product->title,
                'description' => $lotteryRecord->product->description,
                'image_url' => $lotteryRecord->product->image_url,
                'images' => $lotteryRecord->product->images,
                'price' => $lotteryRecord->product->price,
                'category' => $lotteryRecord->product->category->name,
            ],
            'winner' => [
                'name' => substr($lotteryRecord->winner->first_name, 0, 1) . '****',
                'initial' => substr($lotteryRecord->winner->first_name, 0, 1),
                'city' => $lotteryRecord->winner->city ?? 'Non spécifiée',
                'winning_date' => $lotteryRecord->draw_date,
            ],
            'winning_ticket' => [
                'number' => $winningTicket?->ticket_number,
                'purchase_date' => $winningTicket?->created_at,
            ],
            'statistics' => [
                'total_tickets' => $lotteryRecord->total_tickets, // Uses accessor for API compatibility
                'sold_tickets' => $lotteryRecord->sold_tickets,
                'participation_rate' => round(($lotteryRecord->sold_tickets / $lotteryRecord->max_tickets) * 100, 2),
                'total_revenue' => $lotteryRecord->sold_tickets * $lotteryRecord->ticket_price,
                'ticket_price' => $lotteryRecord->ticket_price,
            ],
            'timeline' => [
                'start_date' => $lotteryRecord->start_date,
                'end_date' => $lotteryRecord->end_date,
                'draw_date' => $lotteryRecord->draw_date,
                'duration_days' => Carbon::parse($lotteryRecord->start_date)->diffInDays($lotteryRecord->end_date),
            ],
            'verification' => [
                'code' => $this->generateVerificationCode($lotteryRecord),
                'draw_proof' => $lotteryRecord->draw_proof,
                'is_verified' => true,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/public/results/verify/{code}",
     *     tags={"Public Results"},
     *     summary="Vérifier un code de résultat",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Code de vérification",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Résultat de la vérification")
     * )
     */
    public function verify($code)
    {
        // Le code de vérification est basé sur le hash du lottery_id + draw_date
        $lotteries = Lottery::where('status', 'completed')
            ->whereNotNull('winner_user_id')
            ->get();

        foreach ($lotteries as $lottery) {
            if ($this->generateVerificationCode($lottery) === $code) {
                return $this->sendResponse([
                    'valid' => true,
                    'lottery_number' => $lottery->lottery_number,
                    'product_title' => $lottery->product->title,
                    'draw_date' => $lottery->draw_date,
                    'winner_initial' => substr($lottery->winner->first_name, 0, 1),
                    'verification_date' => now(),
                ]);
            }
        }

        return $this->sendError('Code de vérification invalide', [], 404);
    }

    /**
     * @OA\Get(
     *     path="/api/public/results/stats",
     *     tags={"Public Results"},
     *     summary="Statistiques publiques des tombolas",
     *     @OA\Response(response=200, description="Statistiques publiques")
     * )
     */
    public function stats()
    {
        $stats = $this->getPublicStats();

        return $this->sendResponse($stats);
    }

    /**
     * @OA\Get(
     *     path="/api/public/results/recent-winners",
     *     tags={"Public Results"},
     *     summary="Gagnants récents",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de gagnants",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(response=200, description="Liste des gagnants récents")
     * )
     */
    public function recentWinners(Request $request)
    {
        $limit = min($request->get('limit', 10), 20);

        $winners = Lottery::with(['product', 'winner'])
            ->where('status', 'completed')
            ->whereNotNull('winner_user_id')
            ->orderBy('draw_date', 'desc')
            ->limit($limit)
            ->get();

        return $this->sendResponse([
            'winners' => $winners->map(function ($lottery) {
                return [
                    'lottery_number' => $lottery->lottery_number,
                    'product_title' => $lottery->product->title,
                    'product_image' => $lottery->product->image_url,
                    'product_value' => $lottery->product->price,
                    'winner_initial' => substr($lottery->winner->first_name, 0, 1),
                    'winner_city' => $lottery->winner->city ?? 'Non spécifiée',
                    'draw_date' => $lottery->draw_date,
                    'days_ago' => Carbon::parse($lottery->draw_date)->diffInDays(now()),
                ];
            })
        ]);
    }

    /**
     * Générer les statistiques publiques
     */
    protected function getPublicStats($startDate = null)
    {
        $query = Lottery::where('status', 'completed');
        
        if ($startDate) {
            $query->where('draw_date', '>=', $startDate);
        }

        $completedLotteries = $query->get();

        return [
            'total_lotteries' => $completedLotteries->count(),
            'total_winners' => $completedLotteries->whereNotNull('winner_user_id')->count(),
            'total_prizes_value' => $completedLotteries->sum(function($lottery) {
                return $lottery->product->price;
            }),
            'total_tickets_sold' => $completedLotteries->sum('sold_tickets'),
            'average_participation_rate' => $completedLotteries->avg(function($lottery) {
                return $lottery->max_tickets > 0 ? ($lottery->sold_tickets / $lottery->max_tickets) * 100 : 0;
            }),
            'biggest_prize' => [
                'value' => $completedLotteries->max(function($lottery) {
                    return $lottery->product->price;
                }),
                'product' => $completedLotteries->sortByDesc(function($lottery) {
                    return $lottery->product->price;
                })->first()?->product->title,
            ],
            'most_popular_category' => $this->getMostPopularCategory($completedLotteries),
        ];
    }

    /**
     * Générer un code de vérification unique
     */
    protected function generateVerificationCode(Lottery $lottery)
    {
        return strtoupper(substr(md5($lottery->id . $lottery->draw_date . $lottery->lottery_number), 0, 8));
    }

    /**
     * Obtenir la catégorie la plus populaire
     */
    protected function getMostPopularCategory($lotteries)
    {
        $categories = $lotteries->groupBy(function($lottery) {
            return $lottery->product->category->name;
        });

        $mostPopular = $categories->sortByDesc(function($group) {
            return $group->count();
        })->first();

        return $mostPopular ? [
            'name' => $mostPopular->first()->product->category->name,
            'count' => $mostPopular->count()
        ] : null;
    }
}