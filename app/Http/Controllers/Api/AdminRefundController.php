<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Lottery;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
     * @OA\Get(
     *     path="/api/admin/refunds/{id}",
     *     tags={"Admin Refunds"},
     *     summary="Afficher le détail d'un remboursement",
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
        $refund = Refund::with(['user', 'transaction', 'lottery.product', 'processedBy', 'approvedBy'])
            ->findOrFail($id);

        return $this->sendResponse(['refund' => $refund]);
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

        Log::info('Admin approving refund', [
            'refund_id' => $refund->id,
            'refund_number' => $refund->refund_number,
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'current_status' => $refund->status,
            'amount' => $refund->amount
        ]);

        if ($refund->status !== 'pending') {
            Log::warning('Cannot approve refund - invalid status', [
                'refund_id' => $refund->id,
                'current_status' => $refund->status
            ]);
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
                Log::info('Refund approved successfully', [
                    'refund_id' => $refund->id,
                    'refund_number' => $refund->refund_number,
                    'admin_id' => $admin->id,
                    'new_status' => $refund->fresh()->status
                ]);

                return $this->sendResponse([
                    'message' => 'Remboursement approuvé et traité avec succès',
                    'refund' => $refund->fresh()
                ]);
            } else {
                Log::error('Refund approval failed', [
                    'refund_id' => $refund->id,
                    'admin_id' => $admin->id
                ]);
                return $this->sendError('Erreur lors du traitement du remboursement', [], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception during refund approval', [
                'refund_id' => $refund->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
        $admin = auth()->user();

        Log::info('Admin initiating manual refund process', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'lottery_id' => $request->get('lottery_id'),
            'dry_run' => $request->boolean('dry_run'),
            'ip' => $request->ip()
        ]);

        $validator = Validator::make($request->all(), [
            'lottery_id' => 'nullable|integer|exists:lotteries,id',
            'dry_run' => 'boolean',
            'force' => 'boolean', // Forcer le traitement même avant le délai de 24h
        ]);

        if ($validator->fails()) {
            return $this->sendError('Données invalides', $validator->errors(), 422);
        }

        $lotteryId = $request->get('lottery_id');
        $dryRun = $request->boolean('dry_run');
        $force = $request->boolean('force', false);

        try {
            if ($lotteryId) {
                // Traiter une tombola spécifique
                $lottery = Lottery::with('product')->findOrFail($lotteryId);

                Log::info('Processing refunds for specific lottery', [
                    'lottery_id' => $lottery->id,
                    'lottery_number' => $lottery->lottery_number,
                    'status' => $lottery->status,
                    'admin_id' => $admin->id,
                    'dry_run' => $dryRun
                ]);

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

                if ($force && $lottery->status === 'active') {
                    // Forcer le remboursement avant le délai
                    $result = $this->refundService->forceRefundLottery($lottery, 'manual_force_admin');
                } else {
                    $reason = $lottery->status === 'cancelled' ? 'lottery_cancelled' : 'insufficient_participants';
                    $result = $this->refundService->processAutomaticRefunds($lottery, $reason);
                }

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
        try {
            // Utiliser le service pour obtenir les tombolas éligibles avec statut détaillé
            $eligibleLotteries = $this->refundService->getEligibleLotteriesForRefund();

            // Tombolas annulées sans remboursements
            $cancelledLotteries = Lottery::where('status', 'cancelled')
                ->whereDoesntHave('refunds')
                ->with('product')
                ->get();

            return $this->sendResponse([
                'expired_insufficient' => collect($eligibleLotteries)->map(function ($item) {
                    $lottery = $item['lottery'];
                    return [
                        'id' => $lottery->id,
                        'lottery_number' => $lottery->lottery_number,
                        'product_title' => $lottery->product->title,
                        'participants' => $item['current_participants'],
                        'min_participants' => $item['min_participants'],
                        'ticket_price' => $lottery->ticket_price,
                        'estimated_refund' => $item['current_participants'] * $lottery->ticket_price,
                        'end_date' => $lottery->end_date,
                        'auto_refund_time' => $item['auto_refund_time'],
                        'can_process_now' => $item['can_process_now'],
                        'can_process_manually' => $item['can_process_manually'],
                        'hours_until_auto' => $item['hours_until_auto'],
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
                        'can_process_now' => true,
                        'can_process_manually' => true,
                    ];
                }),
                'summary' => [
                    'expired_count' => count($eligibleLotteries),
                    'ready_for_auto' => collect($eligibleLotteries)->where('can_process_now', true)->count(),
                    'waiting_for_delay' => collect($eligibleLotteries)->where('can_process_now', false)->count(),
                    'manual_allowed' => collect($eligibleLotteries)->where('can_process_manually', true)->count(),
                    'expired_estimated_refund' => collect($eligibleLotteries)->sum(function ($item) {
                        return $item['current_participants'] * $item['lottery']->ticket_price;
                    }),
                    'cancelled_count' => $cancelledLotteries->count(),
                    'cancelled_estimated_refund' => $cancelledLotteries->sum(function ($lottery) {
                        return $lottery->sold_tickets * $lottery->ticket_price;
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors du chargement des tombolas éligibles', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/refunds/{id}/retry",
     *     tags={"Admin Refunds"},
     *     summary="Relancer un remboursement rejeté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du remboursement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Remboursement relancé")
     * )
     */
    public function retry($id)
    {
        $refund = Refund::findOrFail($id);
        $admin = auth()->user();

        Log::info('Admin retrying rejected refund', [
            'refund_id' => $refund->id,
            'refund_number' => $refund->refund_number,
            'admin_id' => $admin->id,
            'current_status' => $refund->status
        ]);

        if (!in_array($refund->status, ['rejected', 'failed'])) {
            Log::warning('Cannot retry refund - invalid status', [
                'refund_id' => $refund->id,
                'current_status' => $refund->status
            ]);
            return $this->sendError('Seuls les remboursements rejetés ou échoués peuvent être relancés', [], 422);
        }

        try {
            // Réinitialiser le statut à pending
            $refund->update([
                'status' => 'pending',
                'rejection_reason' => null,
                'rejected_at' => null,
                'meta' => array_merge($refund->meta ?? [], [
                    'retry_count' => ($refund->meta['retry_count'] ?? 0) + 1,
                    'retried_at' => now()->toISOString(),
                    'retried_by' => $admin->id
                ])
            ]);

            Log::info('Refund status reset to pending for retry', [
                'refund_id' => $refund->id,
                'retry_count' => $refund->meta['retry_count'] ?? 1
            ]);

            // Retraiter le remboursement
            $success = $this->refundService->approveRefund($refund->fresh(), $admin, 'Relance après rejet');

            if ($success) {
                Log::info('Refund retry successful', [
                    'refund_id' => $refund->id,
                    'new_status' => $refund->fresh()->status
                ]);

                return $this->sendResponse([
                    'message' => 'Remboursement relancé avec succès',
                    'refund' => $refund->fresh()
                ]);
            } else {
                Log::error('Refund retry failed', [
                    'refund_id' => $refund->id
                ]);
                return $this->sendError('Erreur lors du retraitement du remboursement', [], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception during refund retry', [
                'refund_id' => $refund->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Erreur lors de la relance: ' . $e->getMessage(), [], 500);
        }
    }
}