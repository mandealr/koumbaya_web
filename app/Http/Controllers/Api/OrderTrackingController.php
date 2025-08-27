<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\LotteryTicket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderTrackingController extends Controller
{
    /**
     * Récupérer toutes les commandes de l'utilisateur connecté
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 10);
        $status = $request->get('status');
        $type = $request->get('type');

        $query = Transaction::where('user_id', $user->id)
            ->with(['product', 'lottery', 'lottery_tickets'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total()
            ]
        ]);
    }

    /**
     * Récupérer les détails d'une commande spécifique
     */
    public function show(Request $request, string $transactionId): JsonResponse
    {
        $user = Auth::user();
        
        $transaction = Transaction::where('user_id', $user->id)
            ->where('transaction_id', $transactionId)
            ->with([
                'product',
                'lottery.product',
                'lottery_tickets',
                'payments'
            ])
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatTransactionDetails($transaction)
        ]);
    }

    /**
     * Récupérer les statistiques des commandes
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total_orders' => Transaction::where('user_id', $user->id)->count(),
            'completed_orders' => Transaction::where('user_id', $user->id)->completed()->count(),
            'pending_orders' => Transaction::where('user_id', $user->id)->pending()->count(),
            'failed_orders' => Transaction::where('user_id', $user->id)->failed()->count(),
            'total_amount_spent' => Transaction::where('user_id', $user->id)
                ->completed()
                ->sum('amount'),
            'total_tickets_purchased' => LotteryTicket::where('user_id', $user->id)
                ->paid()
                ->count(),
            'winning_tickets' => LotteryTicket::where('user_id', $user->id)
                ->winners()
                ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Rechercher des commandes par référence ou numéro de ticket
     */
    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $search = $request->get('q');

        if (empty($search)) {
            return response()->json([
                'success' => false,
                'message' => 'Terme de recherche requis'
            ], 400);
        }

        // Recherche par référence de transaction
        $transactions = Transaction::where('user_id', $user->id)
            ->where(function ($query) use ($search) {
                $query->where('reference', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('external_transaction_id', 'like', "%{$search}%");
            })
            ->with(['product', 'lottery', 'lottery_tickets'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Recherche par numéro de ticket
        $tickets = LotteryTicket::where('user_id', $user->id)
            ->where('ticket_number', 'like', "%{$search}%")
            ->with(['lottery.product', 'transaction'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions,
                'tickets' => $tickets
            ]
        ]);
    }

    /**
     * Formatter les détails d'une transaction
     */
    private function formatTransactionDetails(Transaction $transaction): array
    {
        $details = [
            'id' => $transaction->id,
            'transaction_id' => $transaction->transaction_id,
            'reference' => $transaction->reference,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'status' => $transaction->status,
            'payment_method' => $transaction->payment_method,
            'quantity' => $transaction->quantity,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at,
            'completed_at' => $transaction->completed_at,
            'failed_at' => $transaction->failed_at,
            'failure_reason' => $transaction->failure_reason,
            'expires_at' => $transaction->expires_at,
            'is_expired' => $transaction->is_expired,
        ];

        // Ajouter les informations du produit
        if ($transaction->product) {
            $details['product'] = [
                'id' => $transaction->product->id,
                'name' => $transaction->product->name,
                'description' => $transaction->product->description,
                'image' => $transaction->product->image,
                'sale_mode' => $transaction->product->sale_mode,
            ];
        }

        // Ajouter les informations de la loterie
        if ($transaction->lottery) {
            $details['lottery'] = [
                'id' => $transaction->lottery->id,
                'lottery_number' => $transaction->lottery->lottery_number,
                'title' => $transaction->lottery->title,
                'description' => $transaction->lottery->description,
                'ticket_price' => $transaction->lottery->ticket_price,
                'max_tickets' => $transaction->lottery->max_tickets,
                'sold_tickets' => $transaction->lottery->sold_tickets,
                'status' => $transaction->lottery->status,
                'draw_date' => $transaction->lottery->draw_date,
                'winner_ticket_number' => $transaction->lottery->winner_ticket_number,
            ];
        }

        // Ajouter les billets de loterie
        if ($transaction->lottery_tickets->count() > 0) {
            $details['tickets'] = $transaction->lottery_tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                    'is_winner' => $ticket->is_winner,
                    'price_paid' => $ticket->price_paid,
                    'purchased_at' => $ticket->purchased_at,
                ];
            });
        }

        // Ajouter les informations de paiement
        if ($transaction->payments->count() > 0) {
            $details['payments'] = $transaction->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'billing_id' => $payment->billing_id,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'payment_method' => $payment->payment_method,
                    'created_at' => $payment->created_at,
                ];
            });
        }

        return $details;
    }
}