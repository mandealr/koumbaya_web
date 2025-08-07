<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Lotteries",
 *     description="API endpoints pour la gestion des tombolas"
 * )
 */
class LotteryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show', 'active']]);
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
     *     @OA\Response(response=200, description="Liste des tombolas")
     * )
     */
    public function index(Request $request)
    {
        $query = Lottery::with(['product.category', 'product.merchant']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $lotteries = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'lotteries' => $lotteries
        ]);
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
            ->with(['product.category', 'product.merchant'])
            ->orderBy('end_date', 'asc')
            ->limit(20)
            ->get();

        return response()->json([
            'lotteries' => $lotteries
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
        $user = auth('api')->user();
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
        $user = auth('api')->user();
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
