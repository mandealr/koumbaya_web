<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\EBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Payment Callbacks",
 *     description="Endpoints pour les callbacks de paiement Mobile Money"
 * )
 */
class PaymentCallbackController extends Controller
{
    protected $eBillingService;

    public function __construct(EBillingService $eBillingService)
    {
        $this->eBillingService = $eBillingService;
    }

    /**
     * @OA\Post(
     *     path="/api/payment/callback",
     *     tags={"Payment Callbacks"},
     *     summary="Callback de notification de paiement",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="reference", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="amount", type="number"),
     *             @OA\Property(property="transaction_id", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Callback traité")
     * )
     */
    public function handleCallback(Request $request)
    {
        // Log de la requête pour debugging
        Log::info('Payment callback received:', $request->all());

        try {
            // Récupérer les données du callback
            $reference = $request->input('reference');
            $status = $request->input('status');
            $amount = $request->input('amount');
            $transactionId = $request->input('transaction_id');
            
            // Trouver la transaction
            $transaction = Payment::where('transaction_id', $reference)->first();
            
            if (!$transaction) {
                Log::error('Transaction not found:', ['reference' => $reference]);
                return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            }

            // Vérifier le montant
            if (abs($amount - $transaction->amount) > 0.01) {
                Log::error('Amount mismatch:', [
                    'expected' => $transaction->amount,
                    'received' => $amount,
                    'reference' => $reference
                ]);
                
                $transaction->markAsFailed('Montant incorrect', $request->all());
                return response()->json(['status' => 'error', 'message' => 'Amount mismatch'], 400);
            }

            // Traiter selon le statut
            switch (strtolower($status)) {
                case 'success':
                case 'completed':
                case 'paid':
                    $transaction->markAsCompleted($request->all());
                    Log::info('Transaction completed:', ['reference' => $reference]);
                    
                    // Notification à l'utilisateur (optionnel)
                    $this->notifyUser($transaction, 'success');
                    break;

                case 'failed':
                case 'cancelled':
                case 'rejected':
                    $reason = $request->input('message', 'Paiement échoué');
                    $transaction->markAsFailed($reason, $request->all());
                    Log::info('Transaction failed:', ['reference' => $reference, 'reason' => $reason]);
                    
                    // Notification à l'utilisateur (optionnel)
                    $this->notifyUser($transaction, 'failed');
                    break;

                case 'pending':
                case 'processing':
                    // Laisser en attente
                    Log::info('Transaction still pending:', ['reference' => $reference]);
                    break;

                default:
                    Log::warning('Unknown payment status:', ['status' => $status, 'reference' => $reference]);
            }

            return response()->json(['status' => 'success', 'message' => 'Callback processed']);

        } catch (\Exception $e) {
            Log::error('Payment callback error:', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json(['status' => 'error', 'message' => 'Internal error'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/transactions/{transactionId}/status",
     *     tags={"Payment Callbacks"},
     *     summary="Vérifier le statut d'une transaction",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="transactionId",
     *         in="path",
     *         description="ID de la transaction",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statut de la transaction",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="message", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function checkStatus($transactionId)
    {
        $user = auth()->user();
        
        $transaction = Payment::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$transaction) {
            return $this->sendError('Transaction non trouvée');
        }

        $statusMessage = $this->getStatusMessage($transaction->status);

        return $this->sendResponse([
            'status' => $transaction->status,
            'message' => $statusMessage,
            'amount' => $transaction->amount,
            'created_at' => $transaction->created_at,
            'completed_at' => $transaction->completed_at,
            'is_expired' => $transaction->is_expired,
            'expires_at' => $transaction->expires_at,
        ]);
    }

    /**
     * Obtenir un message descriptif pour le statut
     */
    private function getStatusMessage($status)
    {
        switch ($status) {
            case 'pending':
                return 'Paiement en attente de confirmation';
            case 'payment_initiated':
                return 'Paiement initié. Vérifiez votre téléphone.';
            case 'completed':
                return 'Paiement confirmé avec succès';
            case 'failed':
                return 'Le paiement a échoué';
            case 'cancelled':
                return 'Paiement annulé';
            default:
                return 'Statut inconnu';
        }
    }

    /**
     * Notifier l'utilisateur du résultat du paiement
     */
    private function notifyUser(Transaction $transaction, $result)
    {
        // Ici vous pouvez implémenter:
        // - Notification push mobile
        // - Email de confirmation
        // - SMS de confirmation
        // - Mise à jour temps réel via websockets
        
        Log::info('User notification:', [
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->transaction_id,
            'result' => $result
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/payment/return",
     *     tags={"Payment Callbacks"},
     *     summary="Page de retour après paiement",
     *     @OA\Response(response=200, description="Page de retour")
     * )
     */
    public function handleReturn(Request $request)
    {
        // Cette URL peut être utilisée pour rediriger l'utilisateur après paiement
        // dans le cas d'une interface web
        
        $reference = $request->input('reference');
        $status = $request->input('status');
        
        Log::info('Payment return:', $request->all());
        
        // Rediriger vers l'app mobile ou une page web selon le contexte
        return response()->json([
            'message' => 'Paiement traité',
            'reference' => $reference,
            'status' => $status
        ]);
    }

    /**
     * Endpoint pour tester les callbacks en développement
     */
    public function testCallback(Request $request)
    {
        if (!app()->environment('local')) {
            return response()->json(['error' => 'Disponible en développement seulement'], 403);
        }

        // Simuler un callback de succès
        $testData = [
            'reference' => $request->input('reference', 'TXN-TEST-123'),
            'status' => $request->input('status', 'success'),
            'amount' => $request->input('amount', 5000),
            'transaction_id' => $request->input('transaction_id', 'EXT-TEST-456'),
            'message' => 'Test callback'
        ];

        $testRequest = new Request($testData);
        return $this->handleCallback($testRequest);
    }
}