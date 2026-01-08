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
        // Log de la requête pour debugging avec informations de sécurité
        $securityInfo = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'forwarded_for' => $request->header('X-Forwarded-For'),
            'timestamp' => now()->toISOString()
        ];

        Log::info('Payment callback received', [
            'payload' => $request->all(),
            'security' => $securityInfo,
            'headers' => $request->headers->all()
        ]);

        try {
            // Récupérer les données du callback
            $reference = $request->input('reference');
            $status = $request->input('status');
            $amount = $request->input('amount');
            $transactionId = $request->input('transaction_id');

            Log::info('Callback data extracted', [
                'reference' => $reference,
                'status' => $status,
                'amount' => $amount,
                'transaction_id' => $transactionId
            ]);

            // Trouver le paiement par référence ou transaction_id
            $payment = Payment::where('reference', $reference)
                ->orWhere('transaction_id', $reference)
                ->with(['order', 'user'])
                ->first();

            if (!$payment) {
                Log::error('Payment not found in callback', [
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                    'security' => $securityInfo
                ]);
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }

            Log::info('Payment found for callback', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'current_status' => $payment->status,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'expected_amount' => $payment->amount
            ]);

            // Vérifier le montant
            if (abs($amount - $payment->amount) > 0.01) {
                Log::error('Amount mismatch in callback', [
                    'payment_id' => $payment->id,
                    'expected' => $payment->amount,
                    'received' => $amount,
                    'difference' => abs($amount - $payment->amount),
                    'reference' => $reference,
                    'security' => $securityInfo
                ]);

                $payment->markAsFailed('Montant incorrect', $request->all());
                return response()->json(['status' => 'error', 'message' => 'Amount mismatch'], 400);
            }

            Log::info('Amount verification passed', [
                'payment_id' => $payment->id,
                'amount' => $amount
            ]);

            // Traiter selon le statut
            switch (strtolower($status)) {
                case 'success':
                case 'completed':
                case 'paid':
                    Log::info('Processing successful payment callback', [
                        'payment_id' => $payment->id,
                        'reference' => $reference,
                        'status' => $status,
                        'order_id' => $payment->order_id
                    ]);

                    $payment->markAsCompleted($request->all());

                    Log::info('Payment marked as completed', [
                        'payment_id' => $payment->id,
                        'reference' => $reference,
                        'order_id' => $payment->order_id,
                        'new_status' => $payment->fresh()->status
                    ]);

                    // Notification à l'utilisateur (optionnel)
                    $this->notifyUser($payment, 'success');
                    break;

                case 'failed':
                case 'cancelled':
                case 'rejected':
                    $reason = $request->input('message', 'Paiement échoué');

                    Log::info('Processing failed payment callback', [
                        'payment_id' => $payment->id,
                        'reference' => $reference,
                        'status' => $status,
                        'reason' => $reason,
                        'order_id' => $payment->order_id
                    ]);

                    $payment->markAsFailed($reason, $request->all());

                    Log::info('Payment marked as failed', [
                        'payment_id' => $payment->id,
                        'reference' => $reference,
                        'reason' => $reason,
                        'new_status' => $payment->fresh()->status
                    ]);

                    // Notification à l'utilisateur (optionnel)
                    $this->notifyUser($payment, 'failed');
                    break;

                case 'pending':
                case 'processing':
                    // Laisser en attente
                    Log::info('Transaction still pending', [
                        'payment_id' => $payment->id,
                        'reference' => $reference,
                        'status' => $status
                    ]);
                    break;

                default:
                    Log::warning('Unknown payment status in callback', [
                        'payment_id' => $payment->id,
                        'status' => $status,
                        'reference' => $reference,
                        'security' => $securityInfo
                    ]);
            }

            Log::info('Payment callback processed successfully', [
                'payment_id' => $payment->id,
                'reference' => $reference,
                'final_status' => $payment->fresh()->status
            ]);

            return response()->json(['status' => 'success', 'message' => 'Callback processed']);

        } catch (\Exception $e) {
            Log::error('Payment callback processing exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'security' => $securityInfo ?? null
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

        Log::info('Payment status check requested', [
            'user_id' => $user->id,
            'transaction_id' => $transactionId
        ]);

        $payment = Payment::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$payment) {
            Log::warning('Payment not found for status check', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId
            ]);

            return $this->sendError('Paiement non trouvé');
        }

        $statusMessage = $this->getStatusMessage($payment->status);

        Log::info('Payment status retrieved', [
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'transaction_id' => $transactionId
        ]);

        return $this->sendResponse([
            'status' => $payment->status,
            'message' => $statusMessage,
            'amount' => $payment->amount,
            'created_at' => $payment->created_at,
            'paid_at' => $payment->paid_at,
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
    private function notifyUser(Payment $payment, $result)
    {
        Log::info('CALLBACK :: Starting notification process', [
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'result' => $result,
            'mail_driver' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_from' => config('mail.from.address')
        ]);

        try {
            if ($result === 'success') {
                // Send confirmation email to customer
                if ($payment->user && $payment->user->email) {
                    Log::info('CALLBACK :: Attempting to send customer success email', [
                        'payment_id' => $payment->id,
                        'customer_email' => $payment->user->email,
                        'customer_name' => $payment->user->first_name . ' ' . $payment->user->last_name
                    ]);

                    try {
                        \Mail::to($payment->user->email)->send(new \App\Mail\PaymentConfirmation($payment));

                        Log::info('CALLBACK :: Customer success email sent successfully', [
                            'payment_id' => $payment->id,
                            'customer_email' => $payment->user->email
                        ]);
                    } catch (\Exception $e) {
                        Log::error('CALLBACK :: Failed to send customer success email', [
                            'payment_id' => $payment->id,
                            'customer_email' => $payment->user->email,
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                } else {
                    Log::warning('CALLBACK :: Cannot send customer email - user or email missing', [
                        'payment_id' => $payment->id,
                        'has_user' => $payment->user ? 'yes' : 'no',
                        'has_email' => $payment->user && $payment->user->email ? 'yes' : 'no'
                    ]);
                }

                // Send notification to merchant if applicable
                if ($payment->order && $payment->order->product && $payment->order->product->merchant_id) {
                    $merchant = \App\Models\User::find($payment->order->product->merchant_id);

                    if ($merchant && $merchant->email) {
                        Log::info('CALLBACK :: Attempting to send merchant notification', [
                            'payment_id' => $payment->id,
                            'merchant_id' => $merchant->id,
                            'merchant_email' => $merchant->email
                        ]);

                        try {
                            \Mail::to($merchant->email)->send(new \App\Mail\MerchantPaymentNotification($payment));

                            Log::info('CALLBACK :: Merchant notification sent successfully', [
                                'payment_id' => $payment->id,
                                'merchant_id' => $merchant->id,
                                'merchant_email' => $merchant->email
                            ]);
                        } catch (\Exception $e) {
                            Log::error('CALLBACK :: Failed to send merchant notification', [
                                'payment_id' => $payment->id,
                                'merchant_email' => $merchant->email,
                                'error' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } else {
                        Log::warning('CALLBACK :: Merchant found but no email', [
                            'payment_id' => $payment->id,
                            'merchant_id' => $payment->order->product->merchant_id,
                            'has_merchant' => $merchant ? 'yes' : 'no',
                            'has_email' => $merchant && $merchant->email ? 'yes' : 'no'
                        ]);
                    }
                } else {
                    Log::info('CALLBACK :: No merchant to notify', [
                        'payment_id' => $payment->id,
                        'has_order' => $payment->order ? 'yes' : 'no',
                        'has_product' => $payment->order && $payment->order->product ? 'yes' : 'no',
                        'has_merchant_id' => $payment->order && $payment->order->product && $payment->order->product->merchant_id ? 'yes' : 'no'
                    ]);
                }

                // Send to admin if configured
                $adminEmail = config('mail.admin_email');
                if ($adminEmail) {
                    Log::info('CALLBACK :: Attempting to send admin notification', [
                        'payment_id' => $payment->id,
                        'admin_email' => $adminEmail
                    ]);

                    try {
                        \Mail::to($adminEmail)->send(new \App\Mail\MerchantPaymentNotification($payment));

                        Log::info('CALLBACK :: Admin notification sent successfully', [
                            'payment_id' => $payment->id,
                            'admin_email' => $adminEmail
                        ]);
                    } catch (\Exception $e) {
                        Log::error('CALLBACK :: Failed to send admin notification', [
                            'payment_id' => $payment->id,
                            'admin_email' => $adminEmail,
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                } else {
                    Log::warning('CALLBACK :: Admin email not configured', [
                        'payment_id' => $payment->id,
                        'config_value' => $adminEmail
                    ]);
                }
            }

            Log::info('CALLBACK :: Notification process completed', [
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'order_id' => $payment->order_id,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('CALLBACK :: General exception in notification process', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
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

        Log::info('Payment return page accessed', [
            'reference' => $reference,
            'status' => $status,
            'all_params' => $request->all(),
            'ip' => $request->ip()
        ]);

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
            Log::warning('Test callback attempted in non-local environment', [
                'ip' => $request->ip(),
                'environment' => app()->environment()
            ]);
            return response()->json(['error' => 'Disponible en développement seulement'], 403);
        }

        Log::info('Test callback initiated', [
            'reference' => $request->input('reference'),
            'status' => $request->input('status'),
            'amount' => $request->input('amount')
        ]);

        // Simuler un callback de succès
        $testData = [
            'reference' => $request->input('reference'),
            'status' => $request->input('status', 'success'),
            'amount' => $request->input('amount'),
            'transaction_id' => $request->input('transaction_id', 'EBT-' . time()),
            'message' => 'Test callback'
        ];

        if (!$testData['reference'] || !$testData['amount']) {
            Log::warning('Test callback missing required fields', [
                'received' => $request->all()
            ]);

            return response()->json([
                'error' => 'reference et amount sont requis',
                'usage' => 'POST /api/payment/test-callback avec reference et amount'
            ], 400);
        }

        $testRequest = new Request($testData);
        $response = $this->handleCallback($testRequest);

        Log::info('Test callback completed', [
            'test_data' => $testData,
            'response_status' => $response->status()
        ]);

        return response()->json([
            'test_data' => $testData,
            'callback_result' => $response->getData()
        ]);
    }
}