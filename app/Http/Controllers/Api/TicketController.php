<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\Transaction;
use App\Services\EBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Tickets",
 *     description="API endpoints pour l'achat de tickets de tombola"
 * )
 */
class TicketController extends Controller
{
    protected $eBillingService;

    public function __construct(EBillingService $eBillingService)
    {
        $this->middleware('auth:sanctum');
        $this->eBillingService = $eBillingService;
    }

    /**
     * @OA\Post(
     *     path="/api/tickets/purchase",
     *     tags={"Tickets"},
     *     summary="Acheter des tickets de tombola",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"lottery_id","quantity","phone_number","total_amount"},
     *             @OA\Property(property="lottery_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="phone_number", type="string", example="074123456"),
     *             @OA\Property(property="total_amount", type="number", example=5000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Achat initié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction_id", type="string"),
     *                 @OA\Property(property="tickets", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function purchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lottery_id' => 'required|exists:lotteries,id',
            'quantity' => 'required|integer|min:1|max:10',
            'phone_number' => 'required|string|min:8|max:12',
            'total_amount' => 'required|numeric|min:100',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $user = auth()->user();
        $lottery = Lottery::with('product')->findOrFail($request->lottery_id);

        // Vérifications de validité
        if (!$lottery->canPurchaseTicket()) {
            return $this->sendError('Cette tombola n\'est plus disponible à l\'achat.');
        }

        // Vérifier le prix total
        $expectedAmount = $lottery->ticket_price * $request->quantity;
        if (abs($request->total_amount - $expectedAmount) > 0.01) {
            return $this->sendError('Le montant total ne correspond pas au prix des tickets.');
        }

        // Vérifier la disponibilité des tickets
        if ($lottery->sold_tickets + $request->quantity > $lottery->total_tickets) {
            return $this->sendError('Pas assez de tickets disponibles.');
        }

        DB::beginTransaction();
        try {
            // Créer la transaction
            $transactionId = 'TXN-' . time() . '-' . Str::random(6);
            $transaction = Transaction::create([
                'transaction_id' => $transactionId,
                'reference' => $transactionId, // Utiliser transaction_id comme référence
                'user_id' => $user->id,
                'lottery_id' => $lottery->id,
                'amount' => $request->total_amount,
                'phone_number' => $request->phone_number,
                'quantity' => $request->quantity,
                'status' => 'pending',
                'type' => 'ticket_purchase',
                'description' => "Achat de {$request->quantity} ticket(s) pour la tombola {$lottery->title}",
            ]);

            // Créer les tickets en attente
            $tickets = [];
            for ($i = 0; $i < $request->quantity; $i++) {
                $ticketNumber = $this->generateTicketNumber($lottery);
                
                $ticket = LotteryTicket::create([
                    'lottery_id' => $lottery->id,
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->id,
                    'ticket_number' => $ticketNumber,
                    'status' => 'pending', // En attente de paiement
                    'price_paid' => $lottery->ticket_price,
                    'purchased_at' => now(),
                ]);

                $tickets[] = $ticket;
            }

            // Initier le paiement Mobile Money
            $paymentResult = $this->eBillingService->initiate([
                'amount' => $request->total_amount,
                'phone' => $request->phone_number,
                'reference' => $transaction->transaction_id,
                'description' => "Achat de {$request->quantity} ticket(s) - Tombola {$lottery->lottery_number}",
                'callback_url' => url('/api/payment/callback'),
                'return_url' => url('/api/payment/return'),
            ]);

            if ($paymentResult['success']) {
                // Mettre à jour la transaction avec les détails du paiement
                $transaction->update([
                    'payment_provider_id' => $paymentResult['data']['payment_id'] ?? null,
                    'payment_provider' => $paymentResult['data']['provider'] ?? 'mobile_money',
                    'status' => 'payment_initiated',
                ]);

                DB::commit();

                return $this->sendResponse([
                    'transaction_id' => $transaction->transaction_id,
                    'payment_id' => $paymentResult['data']['payment_id'] ?? null,
                    'tickets' => $tickets->map(function ($ticket) {
                        return [
                            'id' => $ticket->id,
                            'ticket_number' => $ticket->ticket_number,
                            'status' => $ticket->status,
                        ];
                    }),
                    'instructions' => $paymentResult['data']['instructions'] ?? 'Suivez les instructions sur votre téléphone pour confirmer le paiement.',
                ], 'Achat initié avec succès. Confirmez le paiement sur votre téléphone.');

            } else {
                DB::rollback();
                return $this->sendError($paymentResult['message'] ?? 'Erreur lors de l\'initiation du paiement.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Erreur lors de l\'achat: ' . $e->getMessage());
        }
    }

    /**
     * Générer un numéro de ticket unique
     */
    private function generateTicketNumber(Lottery $lottery)
    {
        do {
            $ticketNumber = $lottery->lottery_number . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (LotteryTicket::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    /**
     * @OA\Get(
     *     path="/api/tickets/my-tickets",
     *     tags={"Tickets"},
     *     summary="Mes tickets achetés",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="lottery_id",
     *         in="query",
     *         description="Filtrer par tombola",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste de mes tickets",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function myTickets(Request $request)
    {
        $query = LotteryTicket::where('user_id', auth()->id())
            ->with(['lottery.product', 'transaction'])
            ->where('status', 'paid'); // Seulement les tickets payés

        if ($request->has('lottery_id')) {
            $query->where('lottery_id', $request->lottery_id);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

        return $this->sendResponse($tickets);
    }

    /**
     * @OA\Get(
     *     path="/api/tickets/{id}",
     *     tags={"Tickets"},
     *     summary="Détails d'un ticket",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ticket",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Détails du ticket")
     * )
     */
    public function show($id)
    {
        $ticket = LotteryTicket::with(['lottery.product', 'user', 'transaction'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return $this->sendResponse([
            'ticket' => $ticket,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tickets/{id}/cancel",
     *     tags={"Tickets"},
     *     summary="Annuler un ticket (si non payé)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ticket",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Ticket annulé")
     * )
     */
    public function cancel($id)
    {
        $ticket = LotteryTicket::where('user_id', auth()->id())
            ->findOrFail($id);

        if ($ticket->status !== 'pending') {
            return $this->sendError('Ce ticket ne peut pas être annulé.');
        }

        DB::beginTransaction();
        try {
            $ticket->update(['status' => 'cancelled']);
            
            // Annuler la transaction associée si tous les tickets sont annulés
            $transaction = $ticket->transaction;
            if ($transaction && $transaction->tickets()->where('status', '!=', 'cancelled')->count() === 0) {
                $transaction->update(['status' => 'cancelled']);
            }

            DB::commit();
            
            return $this->sendResponse([], 'Ticket annulé avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Erreur lors de l\'annulation: ' . $e->getMessage());
        }
    }
}