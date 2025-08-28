<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Lottery;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Admin Refunds",
 *     description="API endpoints d'administration des remboursements"
 * )
 */
class AdminRefundController extends Controller
{
    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->middleware(['auth:sanctum', 'admin']);
        $this->refundService = $refundService;
    }

    /**
     * @OA\Get(
     *     path="/api/admin/refunds",
     *     tags={"Admin Refunds"},
     *     summary="Liste administrative des remboursements",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type",
     *         @OA\Schema(type="string", enum={"automatic","manual","admin"})
     *     ),
     *     @OA\Parameter(
     *         name="reason",
     *         in="query",
     *         description="Filtrer par raison",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de remboursements",
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *     @OA\Response(response=200, description="Liste des remboursements")
     * )
     */
    public function index(Request $request)
    {
        $limit = min($request->get('limit', 50), 100);
        $status = $request->get('status');
        $type = $request->get('type');
        $reason = $request->get('reason');

        $query = Refund::with(['user', 'transaction', 'lottery.product', 'processedBy', 'approvedBy'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($reason) {
            $query->where('reason', $reason);
        }

        $refunds = $query->paginate($limit);

        return $this->sendResponse([
            'refunds' => $refunds->items(),
            'pagination' => [
                'current_page' => $refunds->currentPage(),
                'last_page' => $refunds->lastPage(),
                'per_page' => $refunds->perPage(),
                'total' => $refunds->total(),
            ],
            'filters' => [
                'available_statuses' => ['pending', 'approved', 'rejected', 'processed', 'completed', 'failed'],
                'available_types' => ['automatic', 'manual', 'admin'],
                'available_reasons' => Refund::distinct('reason')->pluck('reason'),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/refunds/stats",
     *     tags={"Admin Refunds"},
     *     summary="Statistiques administratives des remboursements",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Statistiques complètes")
     * )
     */
    public function stats()
    {
        $stats = $this->refundService->getRefundStats();
        return $this->sendResponse($stats);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/refunds/{id}/approve",
     *     tags={"Admin Refunds"},
     *     summary="Approuver un remboursement",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du remboursement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="notes", type="string", description="Notes d'approbation")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Remboursement approuvé")
     * )
     */
    public function approve(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);
        $admin = auth()->user();

        if ($refund->status !== 'pending') {
            return $this->sendError('Seuls les remboursements en attente peuvent être approuvés', [], 422);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Données invalides', $validator->errors(), 422);
        }

        try {
            $success = $this->refundService->approveRefund($refund, $admin, $request->notes);

            if ($success) {
                return $this->sendResponse([
                    'message' => 'Remboursement approuvé et traité avec succès',
                    'refund' => $refund->fresh()
                ]);
            } else {
                return $this->sendError('Erreur lors du traitement du remboursement', [], 500);
            }

        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de l\'approbation', [], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/refunds/{id}/reject",
     *     tags={"Admin Refunds"},
     *     summary="Rejeter un remboursement",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du remboursement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", description="Raison du rejet"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Remboursement rejeté")
     * )
     */
    public function reject(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);
        $admin = auth()->user();

        if ($refund->status !== 'pending') {
            return $this->sendError('Seuls les remboursements en attente peuvent être rejetés', [], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Données invalides', $validator->errors(), 422);
        }

        try {
            $success = $this->refundService->rejectRefund($refund, $admin, $request->reason);

            if ($success) {
                return $this->sendResponse([
                    'message' => 'Remboursement rejeté avec succès',
                    'refund' => $refund->fresh()
                ]);
            } else {
                return $this->sendError('Erreur lors du rejet', [], 500);
            }

        } catch (\Exception $e) {
            return $this->sendError('Erreur lors du rejet', [], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/refunds/process-automatic",
     *     tags={"Admin Refunds"},
     *     summary="Traiter les remboursements automatiques",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="lottery_id", type="integer", description="ID de la tombola (optionnel)"),
     *             @OA\Property(property="dry_run", type="boolean", description="Mode test", default=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Remboursements automatiques traités")
     * )
     */
    public function processAutomatic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lottery_id' => 'nullable|integer|exists:lotteries,id',
            'dry_run' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Données invalides', $validator->errors(), 422);
        }

        $lotteryId = $request->get('lottery_id');
        $dryRun = $request->boolean('dry_run');

        try {
            if ($lotteryId) {
                // Traiter une tombola spécifique
                $lottery = Lottery::with('product')->findOrFail($lotteryId);
                
                if ($dryRun) {
                    // Mode test - simuler le traitement
                    $minParticipants = $lottery->product->min_participants ?? 10;
                    $needsRefund = $lottery->sold_tickets < $minParticipants && $lottery->end_date <= now();
                    
                    return $this->sendResponse([
                        'message' => 'Test mode - no changes made',
                        'lottery' => $lottery->lottery_number,
                        'participants' => $lottery->sold_tickets,
                        'min_participants' => $minParticipants,
                        'needs_refund' => $needsRefund,
                        'estimated_refunds' => $needsRefund ? $lottery->sold_tickets : 0,
                        'estimated_amount' => $needsRefund ? $lottery->sold_tickets * $lottery->ticket_price : 0,
                    ]);
                }

                $reason = $lottery->status === 'cancelled' ? 'lottery_cancelled' : 'insufficient_participants';
                $result = $this->refundService->processAutomaticRefunds($lottery, $reason);

                return $this->sendResponse([
                    'message' => 'Traitement terminé',
                    'result' => $result
                ]);

            } else {
                // Traiter toutes les tombolas éligibles
                if ($dryRun) {
                    return $this->sendError('Le mode test n\'est pas supporté pour le traitement global', [], 422);
                }

                $results = $this->refundService->checkAndProcessRefunds();

                $summary = [
                    'insufficient_participants' => count($results['insufficient_participants']),
                    'cancelled_lotteries' => count($results['cancelled_lotteries']),
                    'total_processed' => 0,
                    'total_refunded' => 0,
                ];

                foreach ($results['insufficient_participants'] as $item) {
                    if ($item['result']['success']) {
                        $summary['total_processed'] += $item['result']['participant_count'];
                        $summary['total_refunded'] += $item['result']['total_refunded'];
                    }
                }

                foreach ($results['cancelled_lotteries'] as $item) {
                    if ($item['result']['success']) {
                        $summary['total_processed'] += $item['result']['participant_count'];
                        $summary['total_refunded'] += $item['result']['total_refunded'];
                    }
                }

                return $this->sendResponse([
                    'message' => 'Traitement automatique terminé',
                    'summary' => $summary,
                    'details' => $results
                ]);
            }

        } catch (\Exception $e) {
            return $this->sendError('Erreur lors du traitement automatique: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/refunds/eligible-lotteries",
     *     tags={"Admin Refunds"},
     *     summary="Tombolas éligibles aux remboursements",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste des tombolas éligibles")
     * )
     */
    public function eligibleLotteries()
    {
        // Tombolas expirées avec participants insuffisants
        $expiredLotteries = Lottery::where('draw_date', '<=', now())
            ->where('status', 'active')
            ->with('product')
            ->get()
            ->filter(function ($lottery) {
                $minParticipants = $lottery->product->min_participants ?? 10;
                return $lottery->sold_tickets < $minParticipants;
            });

        // Tombolas annulées sans remboursements
        $cancelledLotteries = Lottery::where('status', 'cancelled')
            ->whereDoesntHave('refunds')
            ->with('product')
            ->get();

        return $this->sendResponse([
            'expired_insufficient' => $expiredLotteries->map(function ($lottery) {
                $minParticipants = $lottery->product->min_participants ?? 10;
                return [
                    'id' => $lottery->id,
                    'lottery_number' => $lottery->lottery_number,
                    'product_title' => $lottery->product->title,
                    'participants' => $lottery->sold_tickets,
                    'min_participants' => $minParticipants,
                    'ticket_price' => $lottery->ticket_price,
                    'estimated_refund' => $lottery->sold_tickets * $lottery->ticket_price,
                    'end_date' => $lottery->end_date,
                ];
            }),
            'cancelled' => $cancelledLotteries->map(function ($lottery) {
                return [
                    'id' => $lottery->id,
                    'lottery_number' => $lottery->lottery_number,
                    'product_title' => $lottery->product->title,
                    'participants' => $lottery->sold_tickets,
                    'ticket_price' => $lottery->ticket_price,
                    'estimated_refund' => $lottery->sold_tickets * $lottery->ticket_price,
                ];
            }),
            'summary' => [
                'expired_count' => $expiredLotteries->count(),
                'expired_estimated_refund' => $expiredLotteries->sum(function ($lottery) {
                    return $lottery->sold_tickets * $lottery->ticket_price;
                }),
                'cancelled_count' => $cancelledLotteries->count(),
                'cancelled_estimated_refund' => $cancelledLotteries->sum(function ($lottery) {
                    return $lottery->sold_tickets * $lottery->ticket_price;
                }),
            ]
        ]);
    }
}