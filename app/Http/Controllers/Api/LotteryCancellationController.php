<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\RefundRequest;
use App\Models\LotteryTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LotteryCancellationController extends Controller
{
    /**
     * Cancel a lottery and create refund requests
     */
    public function cancel(Request $request, $lotteryId)
    {
        $user = auth()->user();
        
        // Find the lottery
        $lottery = Lottery::findOrFail($lotteryId);
        
        // Check if user owns this lottery
        if ($lottery->user_id !== $user->id) {
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
            DB::beginTransaction();

            // Get all paid tickets for this lottery
            $paidTickets = LotteryTicket::where('lottery_id', $lottery->id)
                ->where('status', 'paid')
                ->with(['user', 'order'])
                ->get();

            if ($paidTickets->isEmpty()) {
                // No paid tickets, just cancel the lottery
                $lottery->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $request->reason,
                    'cancellation_reason_type' => $request->reason_type,
                    'cancelled_at' => now(),
                    'cancelled_by' => $user->id
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Tombola annulée avec succès (aucun remboursement nécessaire)',
                    'data' => [
                        'lottery' => $lottery->fresh(),
                        'refunds_created' => 0
                    ]
                ]);
            }

            // Group tickets by user to calculate refund amounts
            $refundsByUser = [];
            foreach ($paidTickets as $ticket) {
                $userId = $ticket->user_id;
                if (!isset($refundsByUser[$userId])) {
                    $refundsByUser[$userId] = [
                        'user' => $ticket->user,
                        'tickets_count' => 0,
                        'total_amount' => 0,
                        'tickets' => []
                    ];
                }
                $refundsByUser[$userId]['tickets_count']++;
                $refundsByUser[$userId]['total_amount'] += $lottery->ticket_price;
                $refundsByUser[$userId]['tickets'][] = $ticket;
            }

            // Create refund requests for each user
            $refundsCreated = 0;
            foreach ($refundsByUser as $userId => $refundData) {
                $refundRequest = RefundRequest::create([
                    'user_id' => $userId,
                    'lottery_id' => $lottery->id,
                    'amount' => $refundData['total_amount'],
                    'reason' => 'Lottery cancellation: ' . $request->reason,
                    'reason_type' => 'lottery_cancellation',
                    'status' => 'pending',
                    'requested_by' => $user->id,
                    'tickets_count' => $refundData['tickets_count'],
                    'phone_number' => $refundData['user']->phone,
                    'refund_method' => 'mobile_money', // Default to mobile money
                    'metadata' => json_encode([
                        'lottery_id' => $lottery->id,
                        'lottery_title' => $lottery->title,
                        'cancellation_reason' => $request->reason,
                        'cancellation_reason_type' => $request->reason_type,
                        'tickets' => $refundData['tickets']->pluck('id')->toArray()
                    ])
                ]);

                // Update tickets status to cancelled
                foreach ($refundData['tickets'] as $ticket) {
                    $ticket->update(['status' => 'cancelled']);
                }

                $refundsCreated++;
            }

            // Update lottery status
            $lottery->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->reason,
                'cancellation_reason_type' => $request->reason_type,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Tombola annulée avec succès. {$refundsCreated} demande(s) de remboursement créée(s).",
                'data' => [
                    'lottery' => $lottery->fresh(),
                    'refunds_created' => $refundsCreated,
                    'total_participants' => count($refundsByUser),
                    'total_refund_amount' => array_sum(array_column($refundsByUser, 'total_amount'))
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error cancelling lottery', [
                'lottery_id' => $lotteryId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'annulation de la tombola'
            ], 500);
        }
    }

    /**
     * Get cancellation details for a lottery
     */
    public function getCancellationDetails($lotteryId)
    {
        $user = auth()->user();
        
        $lottery = Lottery::findOrFail($lotteryId);
        
        // Check if user owns this lottery or is admin
        if ($lottery->user_id !== $user->id && !$user->isAdmin() && !$user->isSuperAdmin()) {
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

        // Get related refund requests
        $refundRequests = RefundRequest::where('lottery_id', $lottery->id)
            ->with(['user'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'lottery' => $lottery,
                'refund_requests' => $refundRequests,
                'summary' => [
                    'total_refunds' => $refundRequests->count(),
                    'total_amount' => $refundRequests->sum('amount'),
                    'pending_refunds' => $refundRequests->where('status', 'pending')->count(),
                    'approved_refunds' => $refundRequests->where('status', 'approved')->count(),
                    'completed_refunds' => $refundRequests->where('status', 'completed')->count()
                ]
            ]
        ]);
    }
}