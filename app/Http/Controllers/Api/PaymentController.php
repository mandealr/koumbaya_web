<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\LotteryTicket;
use App\Models\Lottery;
use App\Models\Product;
use App\Services\EBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        $this->middleware('auth:sanctum', ['except' => ['callback', 'success', 'notify']]);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/initiate-from-transaction",
     *     tags={"Payments"},
     *     summary="Créer un paiement eBilling à partir d'une transaction existante",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_id", "phone", "operator"},
     *             @OA\Property(property="transaction_id", type="string", example="TXN-1234567890-ABC123"),
     *             @OA\Property(property="phone", type="string", example="074123456"),
     *             @OA\Property(property="operator", type="string", enum={"airtel", "moov"}, example="airtel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paiement eBilling créé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="bill_id", type="string"),
     *                 @OA\Property(property="reference", type="string"),
     *                 @OA\Property(property="amount", type="number")
     *             )
     *         )
     *     )
     * )
     */
    public function initiateFromTransaction(Request $request)
    {
        $user = auth('sanctum')->user();

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'phone' => 'required|string|max:20',
            'operator' => 'required|string|in:airtel,moov'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Récupérer la transaction
            $transaction = Transaction::where('transaction_id', $request->transaction_id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction introuvable ou déjà traitée'
                ], 404);
            }

            // Préparer les données pour eBilling
            if ($transaction->type === 'ticket_purchase') {
                $lottery = Lottery::find($transaction->lottery_id);
                if (!$lottery) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tombola introuvable'
                    ], 404);
                }

                $data = (object) [
                    'user' => $user,
                    'lottery_id' => $lottery->id,
                    'quantity' => $transaction->quantity,
                    'amount' => $transaction->amount,
                    'reference' => $transaction->reference,
                    'phone' => $request->phone
                ];

                $type = 'lottery_ticket';
            } elseif ($transaction->type === 'product_purchase') {
                $product = Product::find($transaction->product_id);
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produit introuvable'
                    ], 404);
                }

                $data = (object) [
                    'user' => $user,
                    'product' => $product,
                    'amount' => $transaction->amount,
                    'reference' => $transaction->reference,
                    'phone' => $request->phone
                ];

                $type = 'product_purchase';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Type de transaction non supporté'
                ], 422);
            }

            // Créer le paiement eBilling
            $billId = EBillingService::initiate($type, $data);

            if (!$billId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du paiement eBilling'
                ], 500);
            }

            // Mettre à jour la transaction avec l'ID du paiement
            $transaction->update([
                'payment_provider_id' => $billId,
                'payment_provider' => 'ebilling',
                'phone_number' => $request->phone
            ]);

            // Déclencher automatiquement le push USSD
            $paymentSystemName = $request->operator === 'airtel' ? 'airtelmoney' : 'moovmoney4';
            $ussdResult = EBillingService::pushUssd($billId, $paymentSystemName, $request->phone);

            return response()->json([
                'success' => true,
                'message' => 'Paiement créé avec succès. Push USSD envoyé.',
                'data' => [
                    'bill_id' => $billId,
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount,
                    'transaction_id' => $transaction->transaction_id,
                    'ussd_push' => $ussdResult
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Payment creation from transaction failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/payments/initiate",
     *     tags={"Payments"},
     *     summary="Initier un paiement pour achat de tickets ou produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type"},
     *             @OA\Property(property="type", type="string", enum={"lottery_ticket", "product_purchase"}, example="lottery_ticket"),
     *             @OA\Property(property="lottery_id", type="integer", example=1, description="Required for lottery_ticket type"),
     *             @OA\Property(property="product_id", type="integer", example=1, description="Required for product_purchase type"),
     *             @OA\Property(property="quantity", type="integer", example=1, description="Number of tickets (for lottery_ticket)"),
     *             @OA\Property(property="phone", type="string", example="074123456", description="Phone for payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paiement initié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="bill_id", type="string"),
     *                 @OA\Property(property="reference", type="string"),
     *                 @OA\Property(property="amount", type="number")
     *             )
     *         )
     *     )
     * )
     */
    public function initiate(Request $request)
    {
        $user = auth('sanctum')->user();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:lottery_ticket,product_purchase',
            'lottery_id' => 'required_if:type,lottery_ticket|exists:lotteries,id',
            'product_id' => 'required_if:type,product_purchase|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:10',
            'phone' => 'nullable|string|max:20',
            'operator' => 'nullable|string|in:airtel,moov'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Si phone et operator ne sont pas fournis, créer une transaction et rediriger
            if (!$request->phone || !$request->operator) {
                return $this->createTransactionForPayment($request, $user);
            }

            $data = (object) [
                'user' => $user,
                'reference' => 'KMB_' . strtoupper(Str::random(10))
            ];

            if ($request->type === 'lottery_ticket') {
                $lottery = Lottery::findOrFail($request->lottery_id);

                if ($lottery->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cette tombola n\'est pas active'
                    ], 422);
                }

                $quantity = $request->quantity ?? 1;
                $data->lottery_id = $lottery->id;
                $data->quantity = $quantity;
                $data->amount = $lottery->ticket_price * $quantity;
            } elseif ($request->type === 'product_purchase') {
                $product = Product::findOrFail($request->product_id);
                $data->product = $product;
                $data->amount = $product->price;
            }

            // Update user phone if provided
            if ($request->phone) {
                $user->phone = $request->phone;
                $user->save();
            }

            // Add phone and operator info for USSD push
            $data->phone = $request->phone;
            $data->operator = $request->operator;

            // Initiate e-billing payment
            $billId = EBillingService::initiate($request->type, $data);

            if (!$billId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'initialisation du paiement'
                ], 500);
            }

            // Déclencher automatiquement le push USSD
            $paymentSystemName = $request->operator === 'airtel' ? 'airtelmoney' : 'moovmoney4';
            $ussdResult = EBillingService::pushUssd($billId, $paymentSystemName, $request->phone);

            return response()->json([
                'success' => true,
                'message' => 'Paiement initié avec succès',
                'data' => [
                    'bill_id' => $billId,
                    'reference' => $data->reference,
                    'amount' => $data->amount,
                    'type' => $request->type,
                    'ussd_push' => $ussdResult
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Payment initiation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initiation du paiement',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
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
        return response()->json(['message' => 'Callback handled'], 200);
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
    public function status($billId)
    {
        $user = auth('sanctum')->user();

        // Rechercher par ebilling_id ou par reference
        $payment = Payment::where('ebilling_id', $billId)
            ->orWhere('reference', $billId)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement introuvable'
            ], 404);
        }

        // Vérifier que le paiement appartient à l'utilisateur
        if ($payment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non autorisé'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $payment->status,
                'amount' => $payment->amount,
                'reference' => $payment->reference,
                'error_message' => $payment->error_message ?? null
            ]
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
     * @OA\Post(
     *     path="/api/payments/notify",
     *     tags={"Payments"},
     *     summary="Notification E-Billing (IPN)",
     *     @OA\Response(response=200, description="Notification traitée")
     * )
     */
    public function notify(Request $request)
    {
        $result = EBillingService::processNotification($request->all());
        return response('', $result['status']);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/ussd-push",
     *     tags={"Payments"},
     *     summary="Déclencher un push USSD",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bill_id", "payment_system_name", "payer_msisdn"},
     *             @OA\Property(property="bill_id", type="string", example="BILL123456"),
     *             @OA\Property(property="payment_system_name", type="string", example="airtel_money"),
     *             @OA\Property(property="payer_msisdn", type="string", example="074123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Push USSD envoyé")
     * )
     */
    public function pushUssd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|string',
            'payment_system_name' => 'required|string',
            'payer_msisdn' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = EBillingService::pushUssd(
            $request->bill_id,
            $request->payment_system_name,
            $request->payer_msisdn
        );

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/retry-ussd",
     *     tags={"Payments"},
     *     summary="Relancer le push USSD pour un paiement existant",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bill_id", "operator"},
     *             @OA\Property(property="bill_id", type="string", example="5570928698"),
     *             @OA\Property(property="operator", type="string", enum={"airtel", "moov"}, example="airtel")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Push USSD relancé")
     * )
     */
    public function retryUssd(Request $request)
    {
        $user = auth('sanctum')->user();

        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|string',
            'operator' => 'required|string|in:airtel,moov'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Rechercher le paiement par bill_id
            $payment = Payment::where('ebilling_id', $request->bill_id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement introuvable ou déjà traité'
                ], 404);
            }

            // Relancer le push USSD
            $paymentSystemName = $request->operator === 'airtel' ? 'airtelmoney' : 'moovmoney4';
            $result = EBillingService::pushUssd(
                $request->bill_id,
                $paymentSystemName,
                $payment->customer_phone
            );

            return response()->json([
                'success' => true,
                'message' => 'Push USSD relancé avec succès',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('USSD retry failed: ' . $e->getMessage(), [
                'bill_id' => $request->bill_id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du relancement du push USSD'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/payments/kyc",
     *     tags={"Payments"},
     *     summary="Récupérer les informations KYC",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="operator",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", example="airtel_money")
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", example="074123456")
     *     ),
     *     @OA\Response(response=200, description="Données KYC récupérées")
     * )
     */
    public function kyc(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'operator' => 'required|string',
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = EBillingService::getKyc($request->operator, $request->phone);
        return response()->json($result);
    }

    /**
     * Créer une transaction pour un paiement (utilisé quand phone/operator non fournis)
     */
    private function createTransactionForPayment(Request $request, $user)
    {
        try {
            $reference = 'KMB_' . strtoupper(Str::random(10));
            
            if ($request->type === 'lottery_ticket') {
                $lottery = Lottery::findOrFail($request->lottery_id);

                if ($lottery->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cette tombola n\'est pas active'
                    ], 422);
                }

                $quantity = $request->quantity ?? 1;
                $amount = $lottery->ticket_price * $quantity;

                // Créer la transaction
                $transaction = Transaction::create([
                    'transaction_id' => 'TXN_' . strtoupper(Str::random(12)),
                    'user_id' => $user->id,
                    'type' => 'ticket_purchase',
                    'reference' => $reference,
                    'amount' => $amount,
                    'currency' => 'XAF',
                    'status' => 'pending',
                    'lottery_id' => $lottery->id,
                    'quantity' => $quantity,
                    'description' => "Achat de {$quantity} billet(s) pour la loterie {$lottery->title}"
                ]);

                // Créer les billets de loterie
                for ($i = 0; $i < $quantity; $i++) {
                    LotteryTicket::create([
                        'lottery_id' => $lottery->id,
                        'user_id' => $user->id,
                        'transaction_id' => $transaction->id,
                        'price_paid' => $lottery->ticket_price,
                        'status' => 'pending',
                        'ticket_number' => $lottery->lottery_number . '-T' . str_pad(($lottery->sold_tickets + $i + 1), 4, '0', STR_PAD_LEFT)
                    ]);
                }

            } elseif ($request->type === 'product_purchase') {
                $product = Product::findOrFail($request->product_id);

                // Créer la transaction
                $transaction = Transaction::create([
                    'transaction_id' => 'TXN_' . strtoupper(Str::random(12)),
                    'user_id' => $user->id,
                    'type' => 'direct_purchase',
                    'reference' => $reference,
                    'amount' => $product->price,
                    'currency' => 'XAF',
                    'status' => 'pending',
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'description' => "Achat direct du produit {$product->name}"
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction créée avec succès',
                'redirect_to_payment' => true,
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'reference' => $transaction->reference,
                    'type' => $transaction->type
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Transaction creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la transaction'
            ], 500);
        }
    }
}
