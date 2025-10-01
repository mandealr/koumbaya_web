<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\RefundRequest;
use App\Models\Lottery;
use App\Models\Payment;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Merchant Refunds",
 *     description="API endpoints for merchant refund management"
 * )
 */
class MerchantRefundController extends Controller
{
    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/refunds",
     *     tags={"Merchant Refunds"},
     *     summary="Get merchant refunds",
     *     description="Get paginated list of refunds for merchant's lotteries",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by refund number or customer name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by refund status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "approved", "processed", "completed", "rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by refund type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"automatic", "manual", "admin"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Refunds retrieved successfully"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $merchant = auth()->user();
            
            // Récupérer les tombolas du marchand via les produits
            $merchantLotteryIds = Lottery::whereHas('product', function ($query) use ($merchant) {
                $query->where('merchant_id', $merchant->id);
            })->pluck('id')->toArray();

            $query = Refund::whereIn('lottery_id', $merchantLotteryIds)
                ->with(['user', 'lottery.product', 'transaction'])
                ->orderBy('created_at', 'desc');

            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('refund_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtre par type
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            $refunds = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $refunds->items(),
                'meta' => [
                    'current_page' => $refunds->currentPage(),
                    'last_page' => $refunds->lastPage(),
                    'per_page' => $refunds->perPage(),
                    'total' => $refunds->total(),
                    'from' => $refunds->firstItem(),
                    'to' => $refunds->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching merchant refunds', [
                'merchant_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des remboursements'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/refunds/stats",
     *     tags={"Merchant Refunds"},
     *     summary="Get merchant refund statistics",
     *     description="Get refund statistics for merchant's lotteries",
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully"
     *     )
     * )
     */
    public function stats(): JsonResponse
    {
        try {
            $merchant = auth()->user();
            
            // Récupérer les tombolas du marchand via les produits
            $merchantLotteryIds = Lottery::whereHas('product', function ($query) use ($merchant) {
                $query->where('merchant_id', $merchant->id);
            })->pluck('id')->toArray();

            $refunds = Refund::whereIn('lottery_id', $merchantLotteryIds);

            $stats = [
                'total_refunds' => $refunds->count(),
                'total_amount' => $refunds->sum('amount'),
                'pending_refunds' => $refunds->where('status', 'pending')->count(),
                'approved_refunds' => $refunds->where('status', 'approved')->count(),
                'processed_refunds' => $refunds->where('status', 'processed')->count(),
                'completed_refunds' => $refunds->where('status', 'completed')->count(),
                'rejected_refunds' => $refunds->where('status', 'rejected')->count(),
                'automatic_refunds' => $refunds->where('type', 'automatic')->count(),
                'manual_refunds' => $refunds->where('type', 'manual')->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error fetching merchant refund stats', [
                'merchant_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des statistiques'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/merchant/refunds",
     *     tags={"Merchant Refunds"},
     *     summary="Create refund request",
     *     description="Create a manual refund request for a specific transaction",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_id", "reason"},
     *             @OA\Property(property="transaction_id", type="integer", description="Transaction ID to refund"),
     *             @OA\Property(property="reason", type="string", description="Reason for refund"),
     *             @OA\Property(property="notes", type="string", description="Additional notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Refund request created successfully"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:payments,id',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $merchant = auth()->user();
            $transaction = Payment::findOrFail($request->transaction_id);

            // Vérifier que la transaction appartient à une tombola du marchand
            if ($transaction->lottery->product->merchant_id !== $merchant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à rembourser cette transaction'
                ], 403);
            }

            // Vérifier qu'un remboursement n'existe pas déjà
            $existingRefund = Refund::where('transaction_id', $transaction->id)->first();
            if ($existingRefund) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un remboursement existe déjà pour cette transaction'
                ], 400);
            }

            // Créer la demande de remboursement
            $refund = $this->refundService->createManualRefund(
                $transaction,
                $request->reason,
                $merchant
            );

            if ($request->filled('notes')) {
                $refund->update(['notes' => $request->notes]);
            }

            Log::info('Merchant refund request created', [
                'refund_id' => $refund->id,
                'merchant_id' => $merchant->id,
                'transaction_id' => $transaction->id,
                'amount' => $refund->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande de remboursement créée avec succès',
                'refund' => $refund->load(['user', 'lottery.product', 'transaction'])
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating merchant refund request', [
                'merchant_id' => auth()->id(),
                'transaction_id' => $request->transaction_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la demande de remboursement'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/refunds/{id}",
     *     tags={"Merchant Refunds"},
     *     summary="Get refund details",
     *     description="Get detailed information about a specific refund",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Refund details retrieved successfully"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $merchant = auth()->user();
            
            $refund = Refund::with(['user', 'lottery.product', 'transaction', 'processedBy', 'approvedBy', 'rejectedBy'])
                ->findOrFail($id);

            // Vérifier que le remboursement appartient à une tombola du marchand
            if ($refund->lottery->product->merchant_id !== $merchant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Remboursement non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'refund' => $refund
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Remboursement non trouvé'
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/merchant/refunds/{id}/cancel",
     *     tags={"Merchant Refunds"},
     *     summary="Cancel refund request",
     *     description="Cancel a pending refund request",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Refund request cancelled successfully"
     *     )
     * )
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $merchant = auth()->user();
            
            $refund = Refund::findOrFail($id);

            // Vérifier que le remboursement appartient à une tombola du marchand
            if ($refund->lottery->product->merchant_id !== $merchant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Remboursement non trouvé'
                ], 404);
            }

            // Vérifier que le remboursement peut être annulé
            if ($refund->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les remboursements en attente peuvent être annulés'
                ], 400);
            }

            $refund->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $merchant->id,
                'rejection_reason' => 'Annulé par le marchand'
            ]);

            Log::info('Merchant cancelled refund request', [
                'refund_id' => $refund->id,
                'merchant_id' => $merchant->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande de remboursement annulée'
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling merchant refund', [
                'refund_id' => $id,
                'merchant_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/refunds/eligible-transactions",
     *     tags={"Merchant Refunds"},
     *     summary="Get eligible transactions for refund",
     *     description="Get list of transactions that can be refunded",
     *     @OA\Response(
     *         response=200,
     *         description="Eligible transactions retrieved successfully"
     *     )
     * )
     */
    public function eligibleTransactions(): JsonResponse
    {
        try {
            $merchant = auth()->user();
            
            // Récupérer les transactions des tombolas du marchand qui peuvent être remboursées
            $eligibleTransactions = Payment::whereHas('lottery.product', function ($query) use ($merchant) {
                $query->where('merchant_id', $merchant->id);
            })
            ->where('status', 'completed')
            ->whereDoesntHave('refunds') // Pas de remboursement existant
            ->with(['user', 'lottery.product'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

            return response()->json([
                'success' => true,
                'transactions' => $eligibleTransactions
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching eligible transactions', [
                'merchant_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des transactions'
            ], 500);
        }
    }
}