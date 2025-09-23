<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerchantPayoutRequest;
use App\Models\Order;
use App\Models\Lottery;
use App\Services\MerchantPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MerchantPayoutController extends Controller
{
    protected $payoutService;

    public function __construct(MerchantPayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * Lister les demandes du marchand
     * 
     * @OA\Get(
     *     path="/api/merchant/payout-requests",
     *     summary="Liste des demandes de remboursement du marchand",
     *     tags={"Merchant Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         @OA\Schema(type="string", enum={"pending", "approved", "rejected", "completed", "failed"})
     *     ),
     *     @OA\Response(response=200, description="Liste des demandes")
     * )
     */
    public function index(Request $request)
    {
        $query = MerchantPayoutRequest::byMerchant(auth()->id())
            ->with(['customer', 'order', 'lottery', 'product', 'payout']);

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par date
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($requests);
    }

    /**
     * Créer une demande de remboursement
     * 
     * @OA\Post(
     *     path="/api/merchant/payout-requests",
     *     summary="Créer une demande de remboursement",
     *     tags={"Merchant Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refund_type", "reason", "refund_amount", "customer_id", "customer_phone", "payment_operator"},
     *             @OA\Property(property="order_id", type="integer"),
     *             @OA\Property(property="lottery_id", type="integer"),
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="refund_type", type="string", enum={"order_cancellation", "lottery_cancellation", "product_defect", "customer_request", "other"}),
     *             @OA\Property(property="reason", type="string"),
     *             @OA\Property(property="refund_amount", type="number"),
     *             @OA\Property(property="customer_id", type="integer"),
     *             @OA\Property(property="customer_phone", type="string"),
     *             @OA\Property(property="payment_operator", type="string", enum={"airtelmoney", "moovmoney4"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Demande créée avec succès")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => [
                'required_without:lottery_id',
                'nullable',
                'exists:orders,id'
            ],
            'lottery_id' => [
                'required_without:order_id',
                'nullable',
                'exists:lotteries,id'
            ],
            'product_id' => 'sometimes|exists:products,id',
            'refund_type' => [
                'required',
                Rule::in(['order_cancellation', 'lottery_cancellation', 'product_defect', 'customer_request', 'other'])
            ],
            'reason' => 'required|string|min:10|max:1000',
            'refund_amount' => 'required|numeric|min:500|max:500000',
            'customer_id' => 'required|exists:users,id',
            'customer_phone' => ['required', 'string', 'regex:/^[0-9]{8,11}$/'],
            'payment_operator' => ['required', Rule::in(['airtelmoney', 'moovmoney4'])]
        ]);

        try {
            // Si order_id est fourni, récupérer automatiquement le product_id et customer_id
            if ($request->order_id) {
                $order = Order::find($request->order_id);
                $validated['product_id'] = $order->product_id;
                $validated['customer_id'] = $order->user_id;
            }

            // Si lottery_id est fourni, récupérer automatiquement le product_id
            if ($request->lottery_id) {
                $lottery = Lottery::find($request->lottery_id);
                $validated['product_id'] = $lottery->product_id;
            }

            $payoutRequest = $this->payoutService->createPayoutRequest(
                auth()->user(), 
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Demande de remboursement créée avec succès',
                'data' => $payoutRequest->load(['customer', 'order', 'lottery', 'product'])
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur création demande payout', [
                'merchant_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Voir une demande spécifique
     * 
     * @OA\Get(
     *     path="/api/merchant/payout-requests/{id}",
     *     summary="Détails d'une demande de remboursement",
     *     tags={"Merchant Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Détails de la demande")
     * )
     */
    public function show($id)
    {
        $payoutRequest = MerchantPayoutRequest::where('id', $id)
            ->byMerchant(auth()->id())
            ->with(['customer', 'order', 'lottery', 'product', 'payout', 'approver'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $payoutRequest
        ]);
    }

    /**
     * Stats remboursements du marchand
     * 
     * @OA\Get(
     *     path="/api/merchant/payout-requests/stats",
     *     summary="Statistiques des remboursements du marchand",
     *     tags={"Merchant Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Statistiques")
     * )
     */
    public function stats()
    {
        $stats = $this->payoutService->getMerchantStats(auth()->user());
        
        // Ajouter les demandes récentes
        $stats['recent_requests'] = MerchantPayoutRequest::byMerchant(auth()->id())
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Obtenir les commandes éligibles pour remboursement
     * 
     * @OA\Get(
     *     path="/api/merchant/eligible-orders",
     *     summary="Liste des commandes éligibles au remboursement",
     *     tags={"Merchant Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Liste des commandes")
     * )
     */
    public function eligibleOrders()
    {
        $merchantId = auth()->id();

        // Commandes payées des produits du marchand
        $orders = Order::where('status', 'paid')
            ->whereHas('product', function($query) use ($merchantId) {
                $query->where('user_id', $merchantId);
            })
            ->with(['user', 'product', 'lottery'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->user->name,
                    'customer_id' => $order->user_id,
                    'product_name' => $order->product->name,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at,
                    'type' => $order->lottery_id ? 'lottery' : 'direct',
                    'lottery_number' => $order->lottery ? $order->lottery->lottery_number : null
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Obtenir les tombolas éligibles pour remboursement
     * 
     * @OA\Get(
     *     path="/api/merchant/eligible-lotteries",
     *     summary="Liste des tombolas éligibles au remboursement",
     *     tags={"Merchant Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Liste des tombolas")
     * )
     */
    public function eligibleLotteries()
    {
        $merchantId = auth()->id();

        $lotteries = Lottery::whereIn('status', ['active', 'completed', 'cancelled'])
            ->whereHas('product', function($query) use ($merchantId) {
                $query->where('user_id', $merchantId);
            })
            ->with(['product', 'tickets.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($lottery) {
                return [
                    'id' => $lottery->id,
                    'lottery_number' => $lottery->lottery_number,
                    'product_name' => $lottery->product->name,
                    'status' => $lottery->status,
                    'participants' => $lottery->tickets->unique('user_id')->count(),
                    'total_collected' => $lottery->tickets->sum('price'),
                    'draw_date' => $lottery->draw_date,
                    'created_at' => $lottery->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $lotteries
        ]);
    }

    /**
     * Annuler une demande en attente
     * 
     * @OA\Delete(
     *     path="/api/merchant/payout-requests/{id}",
     *     summary="Annuler une demande en attente",
     *     tags={"Merchant Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Demande annulée")
     * )
     */
    public function cancel($id)
    {
        $payoutRequest = MerchantPayoutRequest::where('id', $id)
            ->byMerchant(auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $payoutRequest->update([
            'status' => 'cancelled'
        ]);

        Log::info('Demande de payout annulée par le marchand', [
            'request_id' => $id,
            'merchant_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande annulée avec succès'
        ]);
    }
}