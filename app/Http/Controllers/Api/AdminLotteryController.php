<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\DrawHistory;
use App\Services\LotteryDrawService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Admin Lotteries",
 *     description="API endpoints pour l'administration des tombolas (accès administrateur uniquement)"
 * )
 */
class AdminLotteryController extends Controller
{
    protected $drawService;
    protected $notificationService;

    public function __construct(LotteryDrawService $drawService, NotificationService $notificationService)
    {
        $this->middleware(['auth:sanctum', 'admin']);
        $this->drawService = $drawService;
        $this->notificationService = $notificationService;
    }

    /**
     * @OA\Get(
     *     path="/api/admin/lotteries",
     *     tags={"Admin Lotteries"},
     *     summary="Liste paginée de toutes les tombolas avec filtres",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut (active, completed, cancelled)",
     *         @OA\Schema(type="string", enum={"active", "completed", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="is_drawn",
     *         in="query",
     *         description="Filtrer par état de tirage",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de tombola ou titre de produit",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filtrer à partir de cette date",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filtrer jusqu'à cette date",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Champ de tri",
     *         @OA\Schema(type="string", default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Ordre de tri",
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tombolas avec pagination",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="per_page", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function index(Request $request)
    {
        $query = Lottery::with(['product', 'winner']);

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->is_drawn !== null) {
            $isDrawn = $request->boolean('is_drawn');
            if ($isDrawn) {
                $query->where('status', 'completed')
                      ->whereNotNull('winner_user_id');
            } else {
                $query->where(function($q) {
                    $q->where('status', '!=', 'completed')
                      ->orWhereNull('winner_user_id');
                });
            }
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('lottery_number', 'like', "%{$request->search}%")
                  ->orWhereHas('product', function($q) use ($request) {
                      $q->where('title', 'like', "%{$request->search}%");
                  });
            });
        }

        if ($request->date_from) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->date_to) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to));
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $lotteries = $query->paginate($request->per_page ?? 20);

        // Add statistics
        foreach ($lotteries as $lottery) {
            $lottery->participants_count = $lottery->paidTickets()->count();
            $lottery->revenue = $lottery->paidTickets()->count() * $lottery->ticket_price;
        }

        return response()->json($lotteries);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/lotteries/statistics",
     *     tags={"Admin Lotteries"},
     *     summary="Statistiques globales des tombolas",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques des tombolas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_lotteries", type="integer", description="Nombre total de tombolas"),
     *             @OA\Property(property="active_lotteries", type="integer", description="Nombre de tombolas actives"),
     *             @OA\Property(property="completed_lotteries", type="integer", description="Nombre de tombolas terminées"),
     *             @OA\Property(property="pending_draws", type="integer", description="Nombre de tirages en attente"),
     *             @OA\Property(property="total_revenue", type="number", description="Revenus totaux"),
     *             @OA\Property(property="total_participants", type="integer", description="Nombre total de participants uniques"),
     *             @OA\Property(property="average_participation_rate", type="number", description="Taux de participation moyen en %"),
     *             @OA\Property(
     *                 property="recent_draws",
     *                 type="array",
     *                 description="10 derniers tirages effectués",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="monthly_revenue",
     *                 type="array",
     *                 description="Revenus mensuels des 12 derniers mois",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="month", type="string", example="2024-01"),
     *                     @OA\Property(property="revenue", type="number")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function statistics()
    {
        $stats = [
            'total_lotteries' => Lottery::count(),
            'active_lotteries' => Lottery::where('status', 'active')->count(),
            'completed_lotteries' => Lottery::where('status', 'completed')->count(),
            'pending_draws' => Lottery::where('status', 'active')
                ->whereNull('winner_user_id')
                ->where('draw_date', '<=', now())
                ->count(),
            'total_revenue' => DB::table('lottery_tickets')
                ->where('status', 'paid')
                ->sum('price'),
            'total_participants' => DB::table('lottery_tickets')
                ->where('status', 'paid')
                ->distinct('user_id')
                ->count('user_id'),
            'average_participation_rate' => Lottery::where('status', 'completed')
                ->whereNotNull('winner_user_id')
                ->selectRaw('AVG(sold_tickets / max_tickets * 100) as rate')
                ->value('rate') ?? 0,
            'recent_draws' => DrawHistory::with(['lottery.product', 'winner'])
                ->orderBy('drawn_at', 'desc')
                ->limit(10)
                ->get()
        ];

        // Monthly revenue chart data
        $stats['monthly_revenue'] = DB::table('lottery_tickets')
            ->selectRaw('DATE_FORMAT(purchased_at, "%Y-%m") as month, SUM(price) as revenue')
            ->where('status', 'paid')
            ->where('purchased_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($stats);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/lotteries/{id}",
     *     tags={"Admin Lotteries"},
     *     summary="Détails complets d'une tombola",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tombola",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la tombola avec participants et historique",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Tombola non trouvée"),
     *     @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function show($id)
    {
        $lottery = Lottery::with([
            'product.merchant',
            'winner',
            'tickets' => function($q) {
                $q->with('user')->orderBy('ticket_number');
            }
        ])->findOrFail($id);

        // Additional stats
        $lottery->participants_list = $lottery->tickets()
            ->where('status', 'paid')
            ->with('user:id,first_name,last_name,email,phone')
            ->get()
            ->groupBy('user_id')
            ->map(function($tickets, $userId) {
                $user = $tickets->first()->user;
                return [
                    'user' => $user,
                    'tickets_count' => $tickets->count(),
                    'tickets' => $tickets->pluck('ticket_number'),
                    'total_spent' => $tickets->sum('price')
                ];
            })->values();

        $lottery->draw_history = DrawHistory::where('lottery_id', $lottery->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($lottery);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/lotteries/{id}/draw",
     *     tags={"Admin Lotteries"},
     *     summary="Déclencher un tirage manuel",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tombola",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tirage effectué avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="winner", type="object"),
     *             @OA\Property(property="draw_history", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur lors du tirage",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Tombola non trouvée"),
     *     @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function draw($id)
    {
        $lottery = Lottery::findOrFail($id);
        
        $result = $this->drawService->performDraw($lottery, [
            'method' => 'manual_admin',
            'initiated_by' => 'admin_' . auth()->id()
        ]);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/lotteries/eligible-for-draw",
     *     tags={"Admin Lotteries"},
     *     summary="Liste des tombolas éligibles pour un tirage",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tombolas prêtes pour le tirage",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="count", type="integer", description="Nombre de tombolas éligibles"),
     *             @OA\Property(
     *                 property="lotteries",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="lottery_number", type="string"),
     *                     @OA\Property(property="product", type="string"),
     *                     @OA\Property(property="end_date", type="string", format="date-time"),
     *                     @OA\Property(property="days_since_end", type="integer"),
     *                     @OA\Property(property="participants", type="integer"),
     *                     @OA\Property(property="min_participants", type="integer"),
     *                     @OA\Property(property="revenue", type="number")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function eligibleForDraw()
    {
        $eligibleLotteries = $this->drawService->getEligibleLotteries();
        
        $lotteries = $eligibleLotteries->map(function($lottery) {
            return [
                'id' => $lottery->id,
                'lottery_number' => $lottery->lottery_number,
                'product' => $lottery->product->title,
                'end_date' => $lottery->end_date,
                'days_since_end' => now()->diffInDays($lottery->end_date),
                'participants' => $lottery->paidTickets()->count(),
                'min_participants' => $lottery->product->min_participants ?? 300,
                'revenue' => $lottery->paidTickets()->count() * $lottery->ticket_price
            ];
        });

        return response()->json([
            'count' => $lotteries->count(),
            'lotteries' => $lotteries
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/lotteries/batch-draw",
     *     tags={"Admin Lotteries"},
     *     summary="Effectuer plusieurs tirages en lot",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="lottery_ids",
     *                 type="array",
     *                 description="Liste des IDs de tombolas à tirer",
     *                 @OA\Items(type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Résultats des tirages en lot",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="summary",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="success", type="integer"),
     *                 @OA\Property(property="failed", type="integer")
     *             ),
     *             @OA\Property(
     *                 property="results",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="lottery_id", type="integer"),
     *                     @OA\Property(property="lottery_number", type="string"),
     *                     @OA\Property(property="success", type="boolean"),
     *                     @OA\Property(property="message", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erreur de validation"),
     *     @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function batchDraw(Request $request)
    {
        $request->validate([
            'lottery_ids' => 'required|array',
            'lottery_ids.*' => 'exists:lotteries,id'
        ]);

        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($request->lottery_ids as $lotteryId) {
            $lottery = Lottery::find($lotteryId);
            
            $result = $this->drawService->performDraw($lottery, [
                'method' => 'batch_admin',
                'initiated_by' => 'admin_' . auth()->id()
            ]);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }

            $results[] = [
                'lottery_id' => $lotteryId,
                'lottery_number' => $lottery->lottery_number,
                'success' => $result['success'],
                'message' => $result['message']
            ];
        }

        return response()->json([
            'summary' => [
                'total' => count($request->lottery_ids),
                'success' => $successCount,
                'failed' => $failCount
            ],
            'results' => $results
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/lotteries/{id}/cancel",
     *     tags={"Admin Lotteries"},
     *     summary="Annuler une tombola et initier les remboursements",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tombola",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="reason", type="string", maxLength=500, description="Raison de l'annulation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tombola annulée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="affected_users", type="integer", description="Nombre d'utilisateurs affectés"),
     *             @OA\Property(property="refunds_initiated", type="integer", description="Nombre de remboursements initiés")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Impossible d'annuler une tombola tirée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erreur lors de l'annulation"),
     *     @OA\Response(response=404, description="Tombola non trouvée"),
     *     @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function cancel($id, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $lottery = Lottery::findOrFail($id);

        if ($lottery->is_drawn) {
            return response()->json(['error' => 'Cannot cancel a drawn lottery'], 422);
        }

        DB::beginTransaction();
        try {
            // Update lottery status
            $lottery->update([
                'status' => 'cancelled',
                'metadata' => array_merge($lottery->metadata ?? [], [
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id(),
                    'cancellation_reason' => $request->reason
                ])
            ]);

            // Trigger refunds
            $paidTickets = $lottery->paidTickets()->get();
            foreach ($paidTickets as $ticket) {
                // Create refund record
                DB::table('refunds')->insert([
                    'user_id' => $ticket->user_id,
                    'lottery_id' => $lottery->id,
                    'ticket_id' => $ticket->id,
                    'amount' => $ticket->price,
                    'reason' => 'lottery_cancelled',
                    'status' => 'pending',
                    'metadata' => json_encode([
                        'cancellation_reason' => $request->reason,
                        'cancelled_by_admin' => auth()->id()
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            // Send notifications
            $this->notificationService->notifyLotteryCancellation($lottery, $request->reason);

            return response()->json([
                'message' => 'Lottery cancelled successfully',
                'affected_users' => $paidTickets->pluck('user_id')->unique()->count(),
                'refunds_initiated' => $paidTickets->count()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to cancel lottery'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/admin/lotteries/{id}",
     *     tags={"Admin Lotteries"},
     *     summary="Mettre à jour les paramètres d'une tombola",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tombola",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="end_date", type="string", format="date-time", description="Nouvelle date de fin"),
     *             @OA\Property(property="draw_date", type="string", format="date-time", description="Nouvelle date de tirage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tombola mise à jour avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="lottery", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Impossible de mettre à jour une tombola tirée ou erreur de validation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Tombola non trouvée"),
     *     @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function update($id, Request $request)
    {
        $lottery = Lottery::findOrFail($id);

        if ($lottery->is_drawn) {
            return response()->json(['error' => 'Cannot update a drawn lottery'], 422);
        }

        $request->validate([
            'end_date' => 'nullable|date|after:now',
            'draw_date' => 'nullable|date|after:end_date'
        ]);

        $lottery->update($request->only(['end_date', 'draw_date']));

        return response()->json([
            'message' => 'Lottery updated successfully',
            'lottery' => $lottery
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/lotteries/{id}/export",
     *     tags={"Admin Lotteries"},
     *     summary="Exporter les données d'une tombola",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tombola",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Données de la tombola pour export",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="lottery", type="object"),
     *             @OA\Property(
     *                 property="participants",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="ticket_number", type="string"),
     *                     @OA\Property(property="user_name", type="string"),
     *                     @OA\Property(property="user_email", type="string"),
     *                     @OA\Property(property="user_phone", type="string"),
     *                     @OA\Property(property="purchased_at", type="string", format="date-time"),
     *                     @OA\Property(property="is_winner", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Tombola non trouvée"),
     *     @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function export($id)
    {
        $lottery = Lottery::with([
            'product',
            'tickets.user',
            'winner'
        ])->findOrFail($id);

        $data = [
            'lottery' => $lottery->toArray(),
            'participants' => $lottery->tickets()
                ->where('status', 'paid')
                ->with('user:id,first_name,last_name,email,phone')
                ->get()
                ->map(function($ticket) {
                    return [
                        'ticket_number' => $ticket->ticket_number,
                        'user_name' => $ticket->user->first_name . ' ' . $ticket->user->last_name,
                        'user_email' => $ticket->user->email,
                        'user_phone' => $ticket->user->phone,
                        'purchased_at' => $ticket->purchased_at,
                        'is_winner' => $ticket->is_winner
                    ];
                })
        ];

        return response()->json($data);
    }

    /**
     * Get all payments for a specific lottery
     */
    public function getLotteryPayments($id)
    {
        $lottery = Lottery::with('product')->findOrFail($id);

        // Récupérer tous les paiements complétés pour cette tombola
        $payments = \App\Models\Payment::where('lottery_id', $id)
            ->where('status', 'completed')
            ->with('user:id,first_name,last_name,email,phone')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'payments' => $payments,
            'lottery' => [
                'id' => $lottery->id,
                'lottery_number' => $lottery->lottery_number,
                'product_title' => $lottery->product->name ?? 'N/A',
                'ticket_price' => $lottery->ticket_price,
                'participants' => $payments->count()
            ]
        ]);
    }
}