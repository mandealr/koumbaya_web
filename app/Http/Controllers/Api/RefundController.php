<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Payment;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Refunds",
 *     description="API endpoints pour la gestion des remboursements"
 * )
 */
class RefundController extends Controller
{
    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->middleware('auth:sanctum');
        $this->refundService = $refundService;
    }

    /**
     * @OA\Get(
     *     path="/api/refunds",
     *     tags={"Refunds"},
     *     summary="Liste des remboursements utilisateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         @OA\Schema(type="string", enum={"pending","approved","processed","completed","rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de remboursements",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des remboursements",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="refunds", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="stats", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $limit = min($request->get('limit', 20), 50);
        $status = $request->get('status');

        $query = $user->refunds()
            ->with(['transaction.lottery.product', 'lottery'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $refunds = $query->limit($limit)->get();

        // Statistiques utilisateur
        $userStats = [
            'total_refunds' => $user->refunds()->count(),
            'total_amount_refunded' => $user->refunds()->completed()->sum('amount'),
            'pending_refunds' => $user->refunds()->pending()->count(),
            'pending_amount' => $user->refunds()->pending()->sum('amount'),
        ];

        return $this->sendResponse([
            'refunds' => $refunds->map(function ($refund) {
                return [
                    'id' => $refund->id,
                    'refund_number' => $refund->refund_number,
                    'amount' => $refund->amount,
                    'currency' => $refund->currency,
                    'reason' => $refund->reason,
                    'type' => $refund->type,
                    'status' => $refund->status,
                    'refund_method' => $refund->refund_method,
                    'auto_processed' => $refund->auto_processed,
                    'created_at' => $refund->created_at,
                    'processed_at' => $refund->processed_at,
                    'notes' => $refund->notes,
                    'rejection_reason' => $refund->rejection_reason,
                    'lottery' => $refund->lottery ? [
                        'lottery_number' => $refund->lottery->lottery_number,
                        'product_title' => $refund->lottery->product->title ?? null,
                    ] : null,
                    'transaction' => [
                        'reference' => $refund->transaction->reference,
                        'amount' => $refund->transaction->amount,
                    ]
                ];
            }),
            'stats' => $userStats
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/refunds",
     *     tags={"Refunds"},
     *     summary="Demander un remboursement",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="transaction_id", type="integer", description="ID de la transaction"),
     *             @OA\Property(property="reason", type="string", description="Raison du remboursement"),
     *             @OA\Property(property="notes", type="string", description="Notes additionnelles")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Demande de remboursement créée")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|integer|exists:transactions,id',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Données invalides', $validator->errors(), 422);
        }

        $user = auth()->user();
        $transaction = Payment::where('id', $request->transaction_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$transaction) {
            return $this->sendError('Transaction non trouvée ou non autorisée', [], 404);
        }

        // Vérifier qu'aucun remboursement n'existe déjà
        $existingRefund = Refund::where('transaction_id', $transaction->id)->first();
        if ($existingRefund) {
            return $this->sendError('Un remboursement existe déjà pour cette transaction', [], 409);
        }

        // Vérifier que la transaction peut être remboursée
        if ($transaction->status !== 'completed') {
            return $this->sendError('Seules les transactions complétées peuvent être remboursées', [], 422);
        }

        try {
            $refund = $this->refundService->createManualRefund(
                $transaction,
                $request->reason,
                $user
            );

            if ($request->notes) {
                $refund->update(['notes' => $request->notes]);
            }

            return $this->sendResponse([
                'message' => 'Demande de remboursement créée avec succès',
                'refund' => [
                    'id' => $refund->id,
                    'refund_number' => $refund->refund_number,
                    'amount' => $refund->amount,
                    'status' => $refund->status,
                    'reason' => $refund->reason,
                    'created_at' => $refund->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de la création de la demande', [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/refunds/{id}",
     *     tags={"Refunds"},
     *     summary="Détails d'un remboursement",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du remboursement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Détails du remboursement")
     * )
     */
    public function show($id)
    {
        $user = auth()->user();
        $refund = $user->refunds()
            ->with(['transaction.lottery.product', 'lottery', 'processedBy', 'approvedBy', 'rejectedBy'])
            ->findOrFail($id);

        return $this->sendResponse([
            'refund' => [
                'id' => $refund->id,
                'refund_number' => $refund->refund_number,
                'amount' => $refund->amount,
                'currency' => $refund->currency,
                'reason' => $refund->reason,
                'type' => $refund->type,
                'status' => $refund->status,
                'refund_method' => $refund->refund_method,
                'auto_processed' => $refund->auto_processed,
                'notes' => $refund->notes,
                'rejection_reason' => $refund->rejection_reason,
                'external_refund_id' => $refund->external_refund_id,
                'callback_data' => $refund->callback_data,
                'created_at' => $refund->created_at,
                'processed_at' => $refund->processed_at,
                'approved_at' => $refund->approved_at,
                'rejected_at' => $refund->rejected_at,
                'lottery' => $refund->lottery ? [
                    'id' => $refund->lottery->id,
                    'lottery_number' => $refund->lottery->lottery_number,
                    'product_title' => $refund->lottery->product->title ?? null,
                    'status' => $refund->lottery->status,
                ] : null,
                'transaction' => [
                    'id' => $refund->transaction->id,
                    'reference' => $refund->transaction->reference,
                    'amount' => $refund->transaction->amount,
                    'status' => $refund->transaction->status,
                ],
                'processed_by' => $refund->processedBy ? [
                    'name' => $refund->processedBy->full_name,
                    'email' => $refund->processedBy->email,
                ] : null,
                'approved_by' => $refund->approvedBy ? [
                    'name' => $refund->approvedBy->full_name,
                    'email' => $refund->approvedBy->email,
                ] : null,
                'rejected_by' => $refund->rejectedBy ? [
                    'name' => $refund->rejectedBy->full_name,
                    'email' => $refund->rejectedBy->email,
                ] : null,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/refunds/stats",
     *     tags={"Refunds"},
     *     summary="Statistiques personnelles de remboursements",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Statistiques de remboursements")
     * )
     */
    public function stats()
    {
        $user = auth()->user();
        
        $stats = [
            'total_refunds' => $user->refunds()->count(),
            'total_amount_refunded' => $user->refunds()->completed()->sum('amount'),
            'pending_refunds' => $user->refunds()->pending()->count(),
            'pending_amount' => $user->refunds()->pending()->sum('amount'),
            'approved_refunds' => $user->refunds()->approved()->count(),
            'rejected_refunds' => $user->refunds()->rejected()->count(),
            'by_status' => $user->refunds()
                ->selectRaw('status, count(*) as count, sum(amount) as total_amount')
                ->groupBy('status')
                ->get()
                ->keyBy('status'),
            'by_reason' => $user->refunds()
                ->selectRaw('reason, count(*) as count, sum(amount) as total_amount')
                ->groupBy('reason')
                ->get()
                ->keyBy('reason'),
            'recent_refunds' => $user->refunds()
                ->with('lottery')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];

        return $this->sendResponse($stats);
    }

    /**
     * @OA\Post(
     *     path="/api/refunds/{id}/cancel",
     *     tags={"Refunds"},
     *     summary="Annuler une demande de remboursement",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du remboursement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Demande annulée")
     * )
     */
    public function cancel($id)
    {
        $user = auth()->user();
        $refund = $user->refunds()->findOrFail($id);

        if ($refund->status !== 'pending') {
            return $this->sendError('Seules les demandes en attente peuvent être annulées', [], 422);
        }

        $refund->reject($user, 'Annulé par l\'utilisateur');

        return $this->sendResponse([
            'message' => 'Demande de remboursement annulée avec succès',
            'refund' => [
                'id' => $refund->id,
                'status' => $refund->status,
                'rejected_at' => $refund->rejected_at,
            ]
        ]);
    }
}