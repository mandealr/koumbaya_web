<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerchantPayoutRequest;
use App\Models\User;
use App\Services\MerchantPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminPayoutController extends Controller
{
    protected $payoutService;

    public function __construct(MerchantPayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * Lister toutes les demandes
     * 
     * @OA\Get(
     *     path="/api/admin/payout-requests",
     *     summary="Liste de toutes les demandes de remboursement",
     *     tags={"Admin Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         @OA\Schema(type="string", enum={"pending", "approved", "rejected", "completed", "failed"})
     *     ),
     *     @OA\Parameter(
     *         name="merchant_id",
     *         in="query",
     *         description="Filtrer par marchand",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Liste des demandes")
     * )
     */
    public function index(Request $request)
    {
        $query = MerchantPayoutRequest::with(['merchant', 'customer', 'order', 'lottery', 'approver']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_number', 'like', "%$search%")
                    ->orWhere('customer_phone', 'like', "%$search%")
                    ->orWhereHas('merchant', function($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('customer', function($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($requests);
    }

    /**
     * Voir une demande spécifique
     * 
     * @OA\Get(
     *     path="/api/admin/payout-requests/{id}",
     *     summary="Détails d'une demande de remboursement",
     *     tags={"Admin Payouts"},
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
        $payoutRequest = MerchantPayoutRequest::with([
            'merchant', 
            'customer', 
            'order.product', 
            'order.lottery',
            'lottery.product',
            'product',
            'payout',
            'approver'
        ])->findOrFail($id);

        // Ajouter des informations supplémentaires
        $response = $payoutRequest->toArray();
        
        // Historique de la commande/tombola si applicable
        if ($payoutRequest->order) {
            $response['order_history'] = [
                'created_at' => $payoutRequest->order->created_at,
                'paid_at' => $payoutRequest->order->paid_at,
                'tickets_count' => $payoutRequest->order->tickets_count ?? 0
            ];
        }

        if ($payoutRequest->lottery) {
            $response['lottery_info'] = [
                'participants' => $payoutRequest->lottery->tickets()->distinct('user_id')->count(),
                'total_tickets' => $payoutRequest->lottery->sold_tickets,
                'status' => $payoutRequest->lottery->status
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    /**
     * Approuver une demande
     * 
     * @OA\Post(
     *     path="/api/admin/payout-requests/{id}/approve",
     *     summary="Approuver une demande de remboursement",
     *     tags={"Admin Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="admin_notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Demande approuvée")
     * )
     */
    public function approve($id, Request $request)
    {
        $validated = $request->validate([
            'admin_notes' => 'sometimes|nullable|string|max:500'
        ]);

        $payoutRequest = MerchantPayoutRequest::where('status', 'pending')->findOrFail($id);

        $success = $this->payoutService->approveAndProcess(
            $payoutRequest,
            auth()->user(),
            $validated['admin_notes'] ?? null
        );

        if ($success) {
            Log::info('Demande de payout approuvée', [
                'request_id' => $id,
                'admin_id' => auth()->id(),
                'amount' => $payoutRequest->refund_amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Remboursement approuvé et traité avec succès'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du traitement du remboursement. Veuillez réessayer.'
        ], 500);
    }

    /**
     * Rejeter une demande
     * 
     * @OA\Post(
     *     path="/api/admin/payout-requests/{id}/reject",
     *     summary="Rejeter une demande de remboursement",
     *     tags={"Admin Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", minLength=10)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Demande rejetée")
     * )
     */
    public function reject($id, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500'
        ]);

        $payoutRequest = MerchantPayoutRequest::where('status', 'pending')->findOrFail($id);

        $this->payoutService->rejectRequest(
            $payoutRequest,
            auth()->user(),
            $validated['reason']
        );

        Log::info('Demande de payout rejetée', [
            'request_id' => $id,
            'admin_id' => auth()->id(),
            'reason' => $validated['reason']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande rejetée'
        ]);
    }

    /**
     * Créer un remboursement direct
     * 
     * @OA\Post(
     *     path="/api/admin/direct-payout",
     *     summary="Créer un remboursement direct",
     *     tags={"Admin Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_id", "customer_phone", "payment_operator", "refund_amount", "reason"},
     *             @OA\Property(property="customer_id", type="integer"),
     *             @OA\Property(property="customer_phone", type="string"),
     *             @OA\Property(property="payment_operator", type="string", enum={"airtelmoney", "moovmoney4"}),
     *             @OA\Property(property="refund_amount", type="number"),
     *             @OA\Property(property="reason", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Remboursement créé")
     * )
     */
    public function createDirectPayout(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'customer_phone' => ['required', 'string', 'regex:/^[0-9]{8,11}$/'],
            'payment_operator' => ['required', Rule::in(['airtelmoney', 'moovmoney4'])],
            'refund_amount' => 'required|numeric|min:500|max:500000',
            'reason' => 'required|string|min:10|max:500'
        ]);

        try {
            $payout = $this->payoutService->createDirectPayout(
                auth()->user(),
                $validated
            );

            if ($payout) {
                Log::info('Remboursement direct créé', [
                    'admin_id' => auth()->id(),
                    'customer_id' => $validated['customer_id'],
                    'amount' => $validated['refund_amount'],
                    'payout_id' => $payout->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Remboursement direct créé avec succès',
                    'data' => $payout
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du remboursement'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Erreur création remboursement direct', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Dashboard stats
     * 
     * @OA\Get(
     *     path="/api/admin/payout-requests/stats",
     *     summary="Statistiques du dashboard admin",
     *     tags={"Admin Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Statistiques")
     * )
     */
    public function dashboardStats()
    {
        $stats = [
            'pending_requests' => MerchantPayoutRequest::pending()->count(),
            'requests_today' => MerchantPayoutRequest::whereDate('created_at', today())->count(),
            'total_amount_pending' => MerchantPayoutRequest::pending()->sum('refund_amount'),
            'total_amount_today' => MerchantPayoutRequest::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('refund_amount'),
            'by_status' => [
                'pending' => MerchantPayoutRequest::pending()->count(),
                'approved' => MerchantPayoutRequest::approved()->count(),
                'completed' => MerchantPayoutRequest::completed()->count(),
                'rejected' => MerchantPayoutRequest::rejected()->count()
            ],
            'by_type' => MerchantPayoutRequest::select('refund_type')
                ->selectRaw('count(*) as count')
                ->selectRaw('sum(refund_amount) as total_amount')
                ->groupBy('refund_type')
                ->get(),
            'recent_requests' => MerchantPayoutRequest::pending()
                ->with(['merchant', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Traiter plusieurs demandes en lot
     * 
     * @OA\Post(
     *     path="/api/admin/payout-requests/batch-approve",
     *     summary="Approuver plusieurs demandes en lot",
     *     tags={"Admin Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"request_ids"},
     *             @OA\Property(property="request_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="admin_notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Demandes traitées")
     * )
     */
    public function batchApprove(Request $request)
    {
        $validated = $request->validate([
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => 'required|integer|exists:merchant_payout_requests,id',
            'admin_notes' => 'sometimes|nullable|string|max:500'
        ]);

        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($validated['request_ids'] as $requestId) {
            $payoutRequest = MerchantPayoutRequest::where('status', 'pending')->find($requestId);
            
            if (!$payoutRequest) {
                $results['failed'][] = [
                    'id' => $requestId,
                    'reason' => 'Demande non trouvée ou déjà traitée'
                ];
                continue;
            }

            $success = $this->payoutService->approveAndProcess(
                $payoutRequest,
                auth()->user(),
                $validated['admin_notes'] ?? null
            );

            if ($success) {
                $results['success'][] = $requestId;
            } else {
                $results['failed'][] = [
                    'id' => $requestId,
                    'reason' => 'Erreur lors du traitement'
                ];
            }
        }

        Log::info('Traitement en lot des demandes de payout', [
            'admin_id' => auth()->id(),
            'success_count' => count($results['success']),
            'failed_count' => count($results['failed'])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Traitement terminé',
            'results' => $results
        ]);
    }

    /**
     * Obtenir la liste des marchands avec demandes
     * 
     * @OA\Get(
     *     path="/api/admin/merchants-with-payouts",
     *     summary="Liste des marchands ayant des demandes",
     *     tags={"Admin Payouts"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Liste des marchands")
     * )
     */
    public function merchantsWithPayouts()
    {
        $merchants = User::whereHas('merchantPayoutRequests')
            ->with(['merchantPayoutRequests' => function($query) {
                $query->select('merchant_id')
                    ->selectRaw('count(*) as total_requests')
                    ->selectRaw('sum(case when status = "pending" then 1 else 0 end) as pending_requests')
                    ->selectRaw('sum(refund_amount) as total_amount')
                    ->groupBy('merchant_id');
            }])
            ->get()
            ->map(function($merchant) {
                $stats = $merchant->merchantPayoutRequests->first();
                return [
                    'id' => $merchant->id,
                    'name' => $merchant->name,
                    'email' => $merchant->email,
                    'total_requests' => $stats->total_requests ?? 0,
                    'pending_requests' => $stats->pending_requests ?? 0,
                    'total_amount' => $stats->total_amount ?? 0
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $merchants
        ]);
    }
}