<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\Refund;
use App\Models\Payment;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LotteryCancellationController extends Controller
{
    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    /**
     * Cancel a lottery and create refund requests
     */
    public function cancel(Request $request, $lotteryId)
    {
        $user = auth()->user();

        // Find the lottery with product relation
        $lottery = Lottery::with('product')->findOrFail($lotteryId);

        // Check if user owns this lottery (via product merchant_id)
        if (!$lottery->product || $lottery->product->merchant_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à annuler cette tombola'
            ], 403);
        }
        
        // Check if lottery can be cancelled
        if ($lottery->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Cette tombola ne peut pas être annulée car elle n\'est pas active'
            ], 400);
        }
        
        // Check if there's already a winner
        if ($lottery->winner_user_id || $lottery->winning_ticket_number) {
            return response()->json([
                'success' => false,
                'message' => 'Cette tombola ne peut pas être annulée car le tirage a déjà eu lieu'
            ], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
            'reason_type' => 'required|string|in:technical_issue,insufficient_participation,merchant_request,product_unavailable,other'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Vérifier s'il y a des transactions payées pour cette tombola
            $paidTransactions = Payment::where('lottery_id', $lottery->id)
                ->where('status', 'completed')
                ->count();

            // Mettre à jour le statut de la tombola
            $lottery->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->reason,
                'cancellation_reason_type' => $request->reason_type,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id
            ]);

            if ($paidTransactions === 0) {
                // Aucun paiement, pas de remboursement nécessaire
                Log::info('Lottery cancelled without refunds', [
                    'lottery_id' => $lottery->id,
                    'reason' => $request->reason
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tombola annulée avec succès (aucun remboursement nécessaire)',
                    'data' => [
                        'lottery' => $lottery->fresh(),
                        'refunds_created' => 0
                    ]
                ]);
            }

            // Utiliser le RefundService pour traiter les remboursements automatiques
            $refundResult = $this->refundService->processAutomaticRefunds(
                $lottery,
                $request->reason_type
            );

            if ($refundResult['success']) {
                Log::info('Lottery cancelled with refunds', [
                    'lottery_id' => $lottery->id,
                    'refunds_created' => $refundResult['participant_count'],
                    'total_amount' => $refundResult['total_refunded']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Tombola annulée avec succès. {$refundResult['participant_count']} remboursement(s) traité(s).",
                    'data' => [
                        'lottery' => $lottery->fresh(),
                        'refunds_created' => $refundResult['participant_count'],
                        'total_participants' => $refundResult['participant_count'],
                        'total_refund_amount' => $refundResult['total_refunded']
                    ]
                ]);
            } else {
                throw new \Exception($refundResult['error'] ?? 'Erreur lors du traitement des remboursements');
            }

        } catch (\Exception $e) {
            Log::error('Error cancelling lottery', [
                'lottery_id' => $lotteryId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'annulation de la tombola: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cancellation details for a lottery
     */
    public function getCancellationDetails($lotteryId)
    {
        $user = auth()->user();

        $lottery = Lottery::with('product')->findOrFail($lotteryId);

        // Check if user owns this lottery (via product merchant_id) or is admin
        $isOwner = $lottery->product && $lottery->product->merchant_id === $user->id;
        $isAdmin = method_exists($user, 'isAdmin') && ($user->isAdmin() || $user->isSuperAdmin());

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        if ($lottery->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cette tombola n\'est pas annulée'
            ], 400);
        }

        // Get related refunds (using Refund model, not RefundRequest)
        $refunds = Refund::where('lottery_id', $lottery->id)
            ->with(['user', 'transaction'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'lottery' => $lottery,
                'refunds' => $refunds,
                'summary' => [
                    'total_refunds' => $refunds->count(),
                    'total_amount' => $refunds->sum('amount'),
                    'pending_refunds' => $refunds->where('status', 'pending')->count(),
                    'approved_refunds' => $refunds->where('status', 'approved')->count(),
                    'processed_refunds' => $refunds->where('status', 'processed')->count(),
                    'completed_refunds' => $refunds->where('status', 'completed')->count(),
                    'rejected_refunds' => $refunds->where('status', 'rejected')->count()
                ]
            ]
        ]);
    }
}