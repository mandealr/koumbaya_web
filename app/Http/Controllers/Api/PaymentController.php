<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\LotteryTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="API endpoints pour la gestion des paiements E-Billing"
 * )
 */
class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['callback', 'success']]);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/initiate",
     *     tags={"Payments"},
     *     summary="Initier un paiement E-Billing",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_id"},
     *             @OA\Property(property="transaction_id", type="integer", example=1),
     *             @OA\Property(property="success_url", type="string", example="http://localhost:3000/payment/success"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paiement initié",
     *         @OA\JsonContent(
     *             @OA\Property(property="payment", type="object"),
     *             @OA\Property(property="ebilling_url", type="string"),
     *             @OA\Property(property="redirect_required", type="boolean")
     *         )
     *     )
     * )
     */
    public function initiate(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:transactions,id',
            'success_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = Transaction::findOrFail($request->transaction_id);

        // Vérifier que la transaction appartient à l'utilisateur
        if ($transaction->user_id !== $user->id) {
            return response()->json(['error' => 'Transaction non autorisée'], 403);
        }

        // Vérifier que la transaction n'est pas déjà payée
        if ($transaction->status !== 'created') {
            return response()->json(['error' => 'Transaction déjà traitée'], 422);
        }

        DB::beginTransaction();
        try {
            // Créer l'enregistrement de paiement
            $payment = Payment::create([
                'reference' => 'PAY-' . time() . '-' . $user->id,
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'customer_name' => $user->full_name,
                'customer_phone' => $user->phone,
                'customer_email' => $user->email,
                'description' => $transaction->description,
                'payment_gateway' => 'ebilling',
                'success_url' => $request->success_url ?? config('app.url') . '/payment/success',
                'callback_url' => config('app.url') . '/api/payments/callback',
                'status' => 'created',
            ]);

            // Appeler E-Billing API
            $ebillingResponse = $this->callEBillingAPI($payment);

            if (!$ebillingResponse['success']) {
                DB::rollback();
                return response()->json(['error' => 'Erreur lors de l\'initialisation du paiement'], 500);
            }

            // Mettre à jour avec la réponse E-Billing
            $payment->update([
                'ebilling_id' => $ebillingResponse['data']['bill_id'],
                'status' => 'pending',
                'gateway_response' => $ebillingResponse['data'],
            ]);

            // Mettre à jour la transaction
            $transaction->update(['status' => 'pending']);

            DB::commit();

            return response()->json([
                'message' => 'Paiement initié avec succès',
                'payment' => $payment,
                'ebilling_url' => config('app.ebilling_redirect_url', 'https://test.billing-easy.net'),
                'redirect_required' => true,
                'redirect_data' => [
                    'invoice_number' => $ebillingResponse['data']['bill_id'],
                    'merchant_redirect_url' => $payment->success_url,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment initiation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'initiation du paiement'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/payments/callback",
     *     tags={"Payments"},
     *     summary="Callback E-Billing (Webhook)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="reference", type="string"),
     *             @OA\Property(property="transactionid", type="string"),
     *             @OA\Property(property="paymentsystem", type="string"),
     *             @OA\Property(property="amount", type="number")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Callback traité")
     * )
     */
    public function callback(Request $request)
    {
        Log::info('E-Billing callback received: ', $request->all());

        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
            'transactionid' => 'required|string',
            'paymentsystem' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::error('Invalid callback data: ', $validator->errors()->toArray());
            return response()->json(['error' => 'Invalid callback data'], 400);
        }

        DB::beginTransaction();
        try {
            // Trouver le paiement par référence
            $payment = Payment::where('reference', $request->reference)->first();

            if (!$payment) {
                Log::error('Payment not found for reference: ' . $request->reference);
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Marquer le paiement comme payé
            $payment->markAsPaid(
                $request->paymentsystem,
                $request->transactionid,
                $request->all()
            );

            // Marquer la transaction comme payée
            $payment->transaction->update([
                'status' => 'paid',
                'payment_method' => $request->paymentsystem,
                'external_transaction_id' => $request->transactionid,
                'paid_at' => now(),
            ]);

            // Si c'est un achat de tickets, marquer les tickets comme payés
            if ($payment->transaction->type === 'ticket_purchase') {
                LotteryTicket::where('payment_reference', $payment->transaction->reference)
                    ->update(['status' => 'paid']);
            }

            // Marquer comme traité
            $payment->markAsProcessed();

            DB::commit();

            Log::info('Payment processed successfully: ' . $payment->reference);

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Callback processing failed: ' . $e->getMessage());
            return response()->json(['error' => 'Callback processing failed'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/payments/{id}/status",
     *     tags={"Payments"},
     *     summary="Vérifier le statut d'un paiement",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du paiement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Statut du paiement")
     * )
     */
    public function status($id)
    {
        $user = auth('api')->user();
        $payment = Payment::findOrFail($id);

        // Vérifier que le paiement appartient à l'utilisateur
        if ($payment->user_id !== $user->id) {
            return response()->json(['error' => 'Paiement non autorisé'], 403);
        }

        return response()->json([
            'payment' => $payment->load('transaction')
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/success",
     *     tags={"Payments"},
     *     summary="Page de succès de paiement",
     *     @OA\Parameter(
     *         name="reference",
     *         in="query",
     *         description="Référence du paiement",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Page de succès")
     * )
     */
    public function success(Request $request)
    {
        $reference = $request->get('reference');
        
        if ($reference) {
            $payment = Payment::where('reference', $reference)->first();
            if ($payment && $payment->is_paid) {
                return response()->json([
                    'message' => 'Paiement effectué avec succès',
                    'payment' => $payment->load('transaction')
                ]);
            }
        }

        return response()->json([
            'message' => 'Paiement en cours de traitement'
        ]);
    }

    /**
     * Appeler l'API E-Billing
     */
    private function callEBillingAPI($payment)
    {
        try {
            $payload = [
                'amount' => $payment->amount,
                'reference' => $payment->reference,
                'description' => $payment->description,
                'customer_name' => $payment->customer_name,
                'customer_phone' => $payment->customer_phone,
                'customer_email' => $payment->customer_email,
                'success_url' => $payment->success_url,
                'callback_url' => $payment->callback_url,
            ];

            // Configuration E-Billing (à définir dans config)
            $ebillingUrl = config('ebilling.api_url', 'https://lab.billing-easy.net/api/v1/merchant/e_bills');
            $username = config('ebilling.username');
            $password = config('ebilling.password');

            $response = Http::withBasicAuth($username, $password)
                ->post($ebillingUrl, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('E-Billing API error: ', $response->json());
            return ['success' => false];

        } catch (\Exception $e) {
            Log::error('E-Billing API call failed: ' . $e->getMessage());
            return ['success' => false];
        }
    }
}
