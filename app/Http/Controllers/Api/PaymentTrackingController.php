<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
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
            ->with(['order.product', 'order.lottery', 'order.lottery.tickets'])
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
                  ->orWhere('transaction_id', 'like', "%{$search}%");
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
                'order.product',
                'order.lottery.product',
                'order.lottery.tickets'
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
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            })
            ->with(['order.product', 'order.lottery'])
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
            'transaction_id' => $payment->transaction_id,
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

        // Ajouter les informations de la commande
        if ($payment->order) {
            $details['order'] = [
                'id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
                'type' => $payment->order->type,
                'total_amount' => $payment->order->total_amount,
                'currency' => $payment->order->currency,
                'status' => $payment->order->status,
                'created_at' => $payment->order->created_at,
                'paid_at' => $payment->order->paid_at,
                'fulfilled_at' => $payment->order->fulfilled_at,
            ];

            // Ajouter les informations du produit
            if ($payment->order->product) {
                $details['order']['product'] = [
                    'id' => $payment->order->product->id,
                    'name' => $payment->order->product->name,
                    'description' => $payment->order->product->description,
                    'image' => $payment->order->product->image,
                    'price' => $payment->order->product->price,
                ];
            }

            // Ajouter les informations de la loterie
            if ($payment->order->lottery) {
                $details['order']['lottery'] = [
                    'id' => $payment->order->lottery->id,
                    'lottery_number' => $payment->order->lottery->lottery_number,
                    'title' => $payment->order->lottery->title,
                    'description' => $payment->order->lottery->description,
                    'ticket_price' => $payment->order->lottery->ticket_price,
                    'total_tickets' => $payment->order->lottery->total_tickets,
                    'sold_tickets' => $payment->order->lottery->sold_tickets,
                    'status' => $payment->order->lottery->status,
                    'draw_date' => $payment->order->lottery->draw_date,
                    'winner_ticket_number' => $payment->order->lottery->winner_ticket_number,
                ];

                // Ajouter les billets de loterie
                if ($payment->order->lottery->tickets->count() > 0) {
                    $details['order']['tickets'] = $payment->order->lottery->tickets->map(function ($ticket) {
                        return [
                            'id' => $ticket->id,
                            'ticket_number' => $ticket->ticket_number,
                            'status' => $ticket->status,
                            'is_winner' => $ticket->is_winner ?? false,
                            'price_paid' => $ticket->price,
                            'purchased_at' => $ticket->purchased_at,
                        ];
                    });
                }
            }
        }

        // Informations de la passerelle de paiement depuis les meta
        $details['gateway'] = [
            'name' => $payment->payment_gateway,
            'display_name' => $payment->payment_method ? ucfirst($payment->payment_method) : 'E-Billing',
        ];

        return $details;
    }
}