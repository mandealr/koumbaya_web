<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\Transaction;
use App\Models\Product;
use App\Services\EBillingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Lotteries",
 *     description="API endpoints pour la gestion des tombolas"
 * )
 */
class LotteryController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show', 'active']]);
        $this->notificationService = $notificationService;
    }

    /**
     * @OA\Get(
     *     path="/api/lotteries",
     *     tags={"Lotteries"},
     *     summary="Lister toutes les tombolas",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","active","completed","cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filtrer par catégorie de produit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="min_ticket_price",
     *         in="query",
     *         description="Prix minimum du ticket",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="max_ticket_price",
     *         in="query",
     *         description="Prix maximum du ticket",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="ending_soon",
     *         in="query",
     *         description="Tombolas se terminant dans les 24h",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Trier par",
     *         required=false,
     *         @OA\Schema(type="string", enum={"end_date_asc", "end_date_desc", "ticket_price_asc", "ticket_price_desc", "popularity"})
     *     ),
     *     @OA\Response(response=200, description="Liste des tombolas")
     * )
     */
    public function index(Request $request)
    {
        $query = Lottery::with(['product.category', 'product.merchant', 'tickets']);

        // Filtres de base
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par catégorie de produit
        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($productQuery) use ($request) {
                $productQuery->where('category_id', $request->category_id);
            });
        }

        // Filtres de prix de ticket
        if ($request->filled('min_ticket_price')) {
            $query->where('ticket_price', '>=', $request->min_ticket_price);
        }

        if ($request->filled('max_ticket_price')) {
            $query->where('ticket_price', '<=', $request->max_ticket_price);
        }

        // Tombolas se terminant bientôt
        if ($request->boolean('ending_soon')) {
            $query->where('end_date', '<=', now()->addHours(24))
                  ->where('end_date', '>', now())
                  ->where('status', 'active');
        }

        // Tri des résultats
        $this->applyLotterySorting($query, $request->get('sort_by', 'end_date_asc'));

        $perPage = min($request->get('per_page', 15), 50);
        $lotteries = $query->paginate($perPage);

        // Ajouter des informations utiles
        $lotteries->getCollection()->transform(function ($lottery) {
            $lottery->append(['time_remaining', 'participation_rate', 'is_ending_soon']);
            return $lottery;
        });

        return response()->json([
            'success' => true,
            'data' => $lotteries,
            'filters' => [
                'available_categories' => \App\Models\Category::whereHas('products.lotteries')->get(['id', 'name']),
                'ticket_price_range' => [
                    'min' => Lottery::min('ticket_price'),
                    'max' => Lottery::max('ticket_price')
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/lotteries/my-lotteries",
     *     tags={"Lotteries"},
     *     summary="Lister mes tombolas en tant que merchant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","active","completed","cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par nom ou description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Trier par",
     *         required=false,
     *         @OA\Schema(type="string", enum={"end_date_asc", "end_date_desc", "ticket_price_asc", "ticket_price_desc", "popularity"})
     *     ),
     *     @OA\Response(response=200, description="Liste de mes tombolas")
     * )
     */
    public function myLotteries(Request $request)
    {
        $user = auth('sanctum')->user();
        
        // Vérifier que l'utilisateur est un merchant
        if ($user->role !== 'MERCHANT') {
            return response()->json(['error' => 'Accès réservé aux merchants'], 403);
        }

        $query = Lottery::with(['product.category', 'tickets'])
            ->whereHas('product', function ($productQuery) use ($user) {
                $productQuery->where('merchant_id', $user->id);
            });

        // Filtres de base
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Recherche par nom ou description du produit
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($productQuery) use ($search) {
                $productQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('description', 'LIKE', "%{$search}%");
            })->orWhere('lottery_number', 'LIKE', "%{$search}%");
        }

        // Tri des résultats
        $this->applyLotterySorting($query, $request->get('sort_by', 'end_date_asc'));

        $perPage = min($request->get('per_page', 15), 50);
        $lotteries = $query->paginate($perPage);

        // Ajouter des informations utiles
        $lotteries->getCollection()->transform(function ($lottery) {
            $lottery->append(['time_remaining', 'participation_rate', 'is_ending_soon', 'can_draw']);
            return $lottery;
        });

        // Calculer les statistiques
        $totalQuery = Lottery::whereHas('product', function ($productQuery) use ($user) {
            $productQuery->where('merchant_id', $user->id);
        });

        $stats = [
            'total' => $totalQuery->count(),
            'active' => $totalQuery->where('status', 'active')->count(),
            'pending' => $totalQuery->where('status', 'pending')->count(),
            'completed' => $totalQuery->where('status', 'completed')->count(),
            'cancelled' => $totalQuery->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $lotteries,
            'stats' => $stats
        ]);
    }

    /**
     * Appliquer le tri aux tombolas
     */
    private function applyLotterySorting($query, $sortBy)
    {
        switch ($sortBy) {
            case 'end_date_asc':
                $query->orderBy('end_date', 'asc');
                break;
            case 'end_date_desc':
                $query->orderBy('end_date', 'desc');
                break;
            case 'ticket_price_asc':
                $query->orderBy('ticket_price', 'asc');
                break;
            case 'ticket_price_desc':
                $query->orderBy('ticket_price', 'desc');
                break;
            case 'popularity':
                $query->withCount('tickets')
                      ->orderBy('tickets_count', 'desc');
                break;
            default:
                $query->orderBy('end_date', 'asc');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/lotteries/active",
     *     tags={"Lotteries"},
     *     summary="Tombolas actives",
     *     @OA\Response(response=200, description="Tombolas actives")
     * )
     */
    public function active()
    {
        $lotteries = Lottery::active()
            ->with(['product.category', 'product.merchant', 'tickets'])
            ->orderBy('end_date', 'asc')
            ->limit(20)
            ->get();

        // Ajouter des informations de temps et participation
        $lotteries->transform(function ($lottery) {
            $lottery->append(['time_remaining', 'participation_rate', 'is_ending_soon']);
            return $lottery;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'lotteries' => $lotteries,
                'stats' => [
                    'total_active' => Lottery::active()->count(),
                    'ending_soon' => Lottery::active()
                        ->where('end_date', '<=', now()->addHours(24))
                        ->count()
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/lotteries/{id}",
     *     tags={"Lotteries"},
     *     summary="Détails d'une tombola",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tombola",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Détails de la tombola")
     * )
     */
    public function show($id)
    {
        $lottery = Lottery::with([
            'product.category',
            'product.merchant',
            'winner',
            'paidTickets.user' => function($query) {
                $query->select('id', 'first_name', 'last_name');
            }
        ])->findOrFail($id);

        return response()->json([
            'lottery' => $lottery
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/lotteries/{id}/buy-ticket",
     *     tags={"Lotteries"},
     *     summary="Acheter un ticket de tombola",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tombola",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="quantity", type="integer", example=1, description="Nombre de tickets")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Ticket(s) acheté(s)")
     * )
     */
    public function buyTicket(Request $request, $id)
    {
        $user = auth('sanctum')->user();
        $lottery = Lottery::findOrFail($id);

        if (!$lottery->canPurchaseTicket()) {
            return response()->json(['error' => 'Cette tombola n\'accepte plus de tickets'], 422);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quantity = $request->quantity;
        $totalAmount = $lottery->ticket_price * $quantity;

        // Vérifier que suffisamment de tickets sont disponibles
        if (($lottery->sold_tickets + $quantity) > $lottery->total_tickets) {
            return response()->json(['error' => 'Pas assez de tickets disponibles'], 422);
        }

        DB::beginTransaction();
        try {
            // Créer la transaction
            $transaction = Transaction::create([
                'reference' => 'TKT-' . time() . '-' . $user->id,
                'user_id' => $user->id,
                'type' => 'ticket_purchase',
                'amount' => $totalAmount,
                'currency' => 'XAF',
                'status' => 'created',
                'lottery_id' => $lottery->id,
                'product_id' => $lottery->product_id,
                'description' => "Achat de {$quantity} ticket(s) pour la tombola {$lottery->lottery_number}",
            ]);

            // Créer les tickets
            $tickets = [];
            for ($i = 0; $i < $quantity; $i++) {
                $ticket = LotteryTicket::create([
                    'ticket_number' => $lottery->lottery_number . '-T' . str_pad(($lottery->sold_tickets + $i + 1), 4, '0', STR_PAD_LEFT),
                    'lottery_id' => $lottery->id,
                    'user_id' => $user->id,
                    'price_paid' => $lottery->ticket_price,
                    'payment_reference' => $transaction->reference,
                    'status' => 'pending',
                    'purchased_at' => now(),
                ]);
                $tickets[] = $ticket;
            }

            // Mettre à jour le nombre de tickets vendus
            $lottery->increment('sold_tickets', $quantity);

            DB::commit();

            return response()->json([
                'message' => "Tickets créés avec succès. Procédez au paiement.",
                'transaction' => $transaction,
                'tickets' => $tickets,
                'payment_required' => true,
                'amount' => $totalAmount
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erreur lors de l\'achat'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/lotteries/{id}/my-tickets",
     *     tags={"Lotteries"},
     *     summary="Mes tickets pour une tombola",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tombola",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Mes tickets")
     * )
     */
    public function myTickets($id)
    {
        $user = auth('sanctum')->user();
        $lottery = Lottery::findOrFail($id);

        $tickets = $lottery->tickets()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'tickets' => $tickets,
            'lottery' => $lottery->load('product')
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/lotteries/{id}/draw",
     *     tags={"Lotteries"},
     *     summary="Effectuer le tirage d'une tombola (Admin/Merchant seulement)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tombola",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Tirage effectué")
     * )
     */
    public function drawLottery($id)
    {
        $user = auth('api')->user();
        $lottery = Lottery::with('product.merchant')->findOrFail($id);

        // Vérifier les permissions (propriétaire du produit ou admin)
        if ($lottery->product->merchant_id !== $user->id && $user->role !== 'MANAGER') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if (!$lottery->can_draw) {
            return response()->json(['error' => 'Conditions de tirage non remplies'], 422);
        }

        DB::beginTransaction();
        try {
            // Récupérer tous les tickets payés
            $paidTickets = $lottery->paidTickets()->get();
            
            if ($paidTickets->count() < $lottery->product->min_participants) {
                DB::rollback();
                return response()->json(['error' => 'Pas assez de participants'], 422);
            }

            // Sélectionner un ticket gagnant aléatoirement
            $winningTicket = $paidTickets->random();

            // Marquer le ticket comme gagnant
            $winningTicket->update(['is_winner' => true]);

            // Mettre à jour la tombola
            $lottery->update([
                'winner_user_id' => $winningTicket->user_id,
                'winner_ticket_number' => $winningTicket->ticket_number,
                'draw_date' => now(),
                'is_drawn' => true,
                'status' => 'completed',
                'draw_proof' => json_encode([
                    'total_participants' => $paidTickets->count(),
                    'draw_method' => 'random_selection',
                    'timestamp' => now()->toISOString(),
                    'drawn_by' => $user->id,
                ])
            ]);

            // Mettre à jour le statut du produit
            $lottery->product->update(['status' => 'sold']);

            DB::commit();

            // Envoyer les notifications après le commit réussi
            try {
                // Notification du gagnant
                $this->notificationService->notifyLotteryWinner(
                    $lottery->fresh(['product', 'winner']), 
                    $winningTicket->user, 
                    $winningTicket
                );

                // Notifications des résultats pour tous les participants
                $this->notificationService->notifyLotteryResult(
                    $lottery->fresh(['product', 'winner']), 
                    $winningTicket->user
                );
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas faire échouer le tirage
                \Log::error('Failed to send draw notifications: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Tirage effectué avec succès',
                'lottery' => $lottery->load(['winner', 'product']),
                'winning_ticket' => $winningTicket->load('user')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erreur lors du tirage'], 500);
        }
    }
}
