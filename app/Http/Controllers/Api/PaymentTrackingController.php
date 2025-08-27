<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PaymentTrackingController extends Controller
{
    /**
     * Récupérer tous les paiements de l'utilisateur connecté
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 10);
        $status = $request->get('status');
        $method = $request->get('method');
        $search = $request->get('search');

        $query = Payment::where('user_id', $user->id)
            ->with(['transaction.product', 'transaction.lottery', 'transaction.lottery_tickets'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('ebilling_id', 'like', "%{$search}%")
                  ->orWhere('external_transaction_id', 'like', "%{$search}%");
            });
        }

        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total()
            ]
        ]);
    }

    /**
     * Récupérer les détails d'un paiement spécifique
     */
    public function show(Request $request, string $paymentId): JsonResponse
    {
        $user = Auth::user();
        
        $payment = Payment::where('user_id', $user->id)
            ->where('id', $paymentId)
            ->with([
                'transaction.product',
                'transaction.lottery.product',
                'transaction.lottery_tickets',
                'gateway'
            ])
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatPaymentDetails($payment)
        ]);
    }

    /**
     * Récupérer les statistiques des paiements
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total_payments' => Payment::where('user_id', $user->id)->count(),
            'successful_payments' => Payment::where('user_id', $user->id)->paid()->count(),
            'pending_payments' => Payment::where('user_id', $user->id)->pending()->count(),
            'failed_payments' => Payment::where('user_id', $user->id)
                ->whereIn('status', ['failed', 'expired'])->count(),
            'total_amount' => Payment::where('user_id', $user->id)
                ->paid()
                ->sum('amount'),
            'payment_methods' => Payment::where('user_id', $user->id)
                ->paid()
                ->select('payment_method')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('payment_method')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Rechercher des paiements
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

        $payments = Payment::where('user_id', $user->id)
            ->where(function ($query) use ($search) {
                $query->where('reference', 'like', "%{$search}%")
                    ->orWhere('ebilling_id', 'like', "%{$search}%")
                    ->orWhere('external_transaction_id', 'like', "%{$search}%");
            })
            ->with(['transaction.product', 'transaction.lottery'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Formatter les détails d'un paiement
     */
    private function formatPaymentDetails(Payment $payment): array
    {
        $details = [
            'id' => $payment->id,
            'reference' => $payment->reference,
            'ebilling_id' => $payment->ebilling_id,
            'external_transaction_id' => $payment->external_transaction_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'payment_method' => $payment->payment_method,
            'payment_gateway' => $payment->payment_gateway,
            'customer_name' => $payment->customer_name,
            'customer_phone' => $payment->customer_phone,
            'customer_email' => $payment->customer_email,
            'description' => $payment->description,
            'created_at' => $payment->created_at,
            'paid_at' => $payment->paid_at,
            'processed_at' => $payment->processed_at,
            'gateway_response' => $payment->gateway_response,
        ];

        // Ajouter les informations de la transaction
        if ($payment->transaction) {
            $details['transaction'] = [
                'id' => $payment->transaction->id,
                'transaction_id' => $payment->transaction->transaction_id,
                'reference' => $payment->transaction->reference,
                'type' => $payment->transaction->type,
                'quantity' => $payment->transaction->quantity,
                'description' => $payment->transaction->description,
                'status' => $payment->transaction->status,
                'created_at' => $payment->transaction->created_at,
            ];

            // Ajouter les informations du produit
            if ($payment->transaction->product) {
                $details['transaction']['product'] = [
                    'id' => $payment->transaction->product->id,
                    'name' => $payment->transaction->product->name,
                    'description' => $payment->transaction->product->description,
                    'image' => $payment->transaction->product->image,
                    'sale_mode' => $payment->transaction->product->sale_mode,
                ];
            }

            // Ajouter les informations de la loterie
            if ($payment->transaction->lottery) {
                $details['transaction']['lottery'] = [
                    'id' => $payment->transaction->lottery->id,
                    'lottery_number' => $payment->transaction->lottery->lottery_number,
                    'title' => $payment->transaction->lottery->title,
                    'description' => $payment->transaction->lottery->description,
                    'ticket_price' => $payment->transaction->lottery->ticket_price,
                    'max_tickets' => $payment->transaction->lottery->max_tickets,
                    'sold_tickets' => $payment->transaction->lottery->sold_tickets,
                    'status' => $payment->transaction->lottery->status,
                    'draw_date' => $payment->transaction->lottery->draw_date,
                    'winner_ticket_number' => $payment->transaction->lottery->winner_ticket_number,
                ];
            }

            // Ajouter les billets de loterie
            if ($payment->transaction->lottery_tickets->count() > 0) {
                $details['transaction']['tickets'] = $payment->transaction->lottery_tickets->map(function ($ticket) {
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
        }

        // Ajouter les informations de la passerelle de paiement
        if ($payment->gateway) {
            $details['gateway'] = [
                'name' => $payment->gateway->name,
                'display_name' => $payment->gateway->display_name,
                'logo' => $payment->gateway->logo,
            ];
        }

        return $details;
    }
}