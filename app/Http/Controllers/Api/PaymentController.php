<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\LotteryTicket;
use App\Models\Lottery;
use App\Models\Product;
use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use App\Services\PaymentStatusMapper;
use App\Services\EBillingService;
use App\Services\MetricsService;
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
    protected MetricsService $metricsService;

    public function __construct(MetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
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
            'order_number' => 'required|string',
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
            // Récupérer l'ordre
            $order = Order::where('order_number', $request->order_number)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->with(['product', 'lottery'])
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande introuvable ou déjà traitée'
                ], 404);
            }

            // Préparer les données pour eBilling
            if ($order->type === 'lottery') {
                if (!$order->lottery) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tombola introuvable'
                    ], 404);
                }

                $data = (object) [
                    'user' => $user,
                    'lottery_id' => $order->lottery->id,
                    'quantity' => 1, // Quantity from lottery tickets count
                    'amount' => $order->total_amount,
                    'reference' => $order->order_number,
                    'phone' => $request->phone
                ];

                $type = 'lottery_ticket';
            } elseif ($order->type === 'direct') {
                if (!$order->product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produit introuvable'
                    ], 404);
                }

                $data = (object) [
                    'user' => $user,
                    'product' => $order->product,
                    'amount' => $order->total_amount,
                    'reference' => $order->order_number,
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

            // Créer un enregistrement de paiement
            $payment = Payment::create([
                'reference' => $order->order_number,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'currency' => 'XAF',
                'customer_phone' => $request->phone,
                'payment_gateway' => 'ebilling',
                'ebilling_id' => $billId,
                'status' => 'pending',
                'description' => "Paiement pour commande {$order->order_number}",
            ]);

            // Déclencher automatiquement le push USSD
            $paymentSystemName = $request->operator === 'airtel' ? 'airtelmoney' : 'moovmoney4';
            $ussdResult = EBillingService::pushUssd($billId, $paymentSystemName, $request->phone);

            return response()->json([
                'success' => true,
                'message' => 'Paiement créé avec succès. Push USSD envoyé.',
                'data' => [
                    'bill_id' => $billId,
                    'reference' => $order->order_number,
                    'amount' => $order->total_amount,
                    'order_number' => $order->order_number,
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
     *     tags={"Payment Callbacks"},
     *     summary="Webhook E-Billing orchestré pour traitement des paiements",
     *     description="Endpoint orchestré pour recevoir les callbacks de E-Billing. Valide le payload, vérifie les montants, mappe les statuts, met à jour les commandes et assure l'idempotence avec sécurité renforcée.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Payload du webhook E-Billing",
     *         @OA\JsonContent(
     *             required={"reference", "amount", "transactionid", "paymentsystem"},
     *             @OA\Property(
     *                 property="reference", 
     *                 type="string", 
     *                 example="KMB-PAY-20240115103000-0001",
     *                 description="Référence unique du paiement généré par notre système"
     *             ),
     *             @OA\Property(
     *                 property="amount", 
     *                 type="number", 
     *                 format="float", 
     *                 example=5000,
     *                 description="Montant du paiement en FCFA"
     *             ),
     *             @OA\Property(
     *                 property="transactionid", 
     *                 type="string", 
     *                 example="EB-1234567890",
     *                 description="ID de transaction externe de E-Billing"
     *             ),
     *             @OA\Property(
     *                 property="paymentsystem", 
     *                 type="string", 
     *                 example="airtelmoney",
     *                 description="Système de paiement utilisé (airtelmoney, moovmoney4, etc.)"
     *             ),
     *             @OA\Property(
     *                 property="status", 
     *                 type="string", 
     *                 example="success",
     *                 description="Statut du paiement retourné par la gateway"
     *             ),
     *             @OA\Property(
     *                 property="message", 
     *                 type="string", 
     *                 example="Transaction successful",
     *                 description="Message optionnel de la gateway",
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="ebilling_id", 
     *                 type="string", 
     *                 example="EB-REF-123456",
     *                 description="ID de référence E-Billing optionnel",
     *                 nullable=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Callback traité avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment processed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payment_id", type="integer", example=123),
     *                 @OA\Property(property="order_id", type="integer", example=456),
     *                 @OA\Property(property="order_number", type="string", example="ORD-67890ABCDE"),
     *                 @OA\Property(property="internal_status", type="string", example="paid"),
     *                 @OA\Property(property="processed_at", type="string", format="date-time", example="2024-01-15T10:35:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Callback déjà traité (idempotence)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment already processed"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payment_id", type="integer", example=123),
     *                 @OA\Property(property="status", type="string", example="paid"),
     *                 @OA\Property(property="processed_at", type="string", format="date-time", example="2024-01-15T10:35:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou de montant",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Amount mismatch detected"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="type", type="string", example="amount_mismatch"),
     *                 @OA\Property(property="expected", type="number", example=5000),
     *                 @OA\Property(property="received", type="number", example=4950),
     *                 @OA\Property(property="tolerance", type="number", example=0.01)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Signature invalide ou problème de sécurité",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid signature"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="type", type="string", example="security_violation"),
     *                 @OA\Property(property="reason", type="string", example="HMAC signature mismatch")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paiement ou commande non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Payment not found"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="type", type="string", example="not_found"),
     *                 @OA\Property(property="reference", type="string", example="KMB-PAY-20240115103000-0001")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données du payload invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="reference", type="array", @OA\Items(type="string", example="The reference field is required")),
     *                 @OA\Property(property="amount", type="array", @OA\Items(type="string", example="The amount must be a number"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error during payment processing"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="type", type="string", example="processing_error"),
     *                 @OA\Property(property="details", type="string", example="Database connection failed", nullable=true)
     *             )
     *         )
     *     )
     * )
     */
    public function callback(Request $request)
    {
        // Collect security information
        $securityInfo = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'forwarded_for' => $request->header('X-Forwarded-For'),
            'real_ip' => $request->header('X-Real-IP'),
            'timestamp' => now()->toISOString()
        ];

        Log::info('Payment callback received', [
            'payload' => $request->all(),
            'security' => $securityInfo,
            'headers' => $request->headers->all()
        ]);

        // Track payment callback metrics
        $this->metricsService->paymentCallbackReceived($request->all());

        try {
            // 1. Validate signature if E-Billing provides it
            $signatureValidation = $this->validateEBillingSignature($request);
            if (!$signatureValidation['valid']) {
                Log::warning('Payment callback signature validation failed', [
                    'reason' => $signatureValidation['reason'],
                    'payload' => $request->all(),
                    'security' => $securityInfo
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 403);
            }

            // 2. Validate payload
            $validator = Validator::make($request->all(), [
                'reference' => 'required|string',
                'amount' => 'required|numeric',
                'transactionid' => 'required|string',
                'paymentsystem' => 'required|string',
                'status' => 'string'
            ]);

            if ($validator->fails()) {
                Log::warning('Payment callback validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'payload' => $request->all(),
                    'security' => $securityInfo
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payload',
                    'errors' => $validator->errors()
                ], 400);
            }

            $reference = $request->input('reference');
            $amount = (float) $request->input('amount');
            $transactionId = $request->input('transactionid');
            $paymentSystem = $request->input('paymentsystem');
            $status = $request->input('status', 'success'); // Default to success for backwards compatibility

            // 3. Find payment by reference or ebilling_id
            $payment = Payment::where('reference', $reference)
                ->orWhere('ebilling_id', $reference)
                ->with(['order'])
                ->first();

            if (!$payment) {
                Log::error('Payment not found for callback', [
                    'reference' => $reference,
                    'ebilling_id' => $reference,
                    'transaction_id' => $transactionId,
                    'security' => $securityInfo
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            if (!$payment->order) {
                Log::error('Payment has no associated order', [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'security' => $securityInfo
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found for payment'
                ], 404);
            }

            // 4. Idempotency check - return early if already processed
            if (in_array($payment->status, [PaymentStatus::PAID->value, PaymentStatus::FAILED->value, PaymentStatus::EXPIRED->value])) {
                Log::info('Payment callback already processed - idempotent response', [
                    'payment_id' => $payment->id,
                    'current_status' => $payment->status,
                    'order_id' => $payment->order->id,
                    'security' => $securityInfo
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Already processed',
                    'data' => [
                        'payment_id' => $payment->id,
                        'order_number' => $payment->order->order_number,
                        'payment_status' => $payment->status,
                        'order_status' => $payment->order->status,
                        'processed_at' => $payment->updated_at
                    ]
                ], 200);
            }

            Log::info('Payment and order found for callback', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
                'current_status' => $payment->status
            ]);

            // 5. Process payment with database transaction and lock
            return DB::transaction(function () use ($payment, $amount, $transactionId, $paymentSystem, $status, $request, $securityInfo) {
                // Re-fetch payment with lock to prevent concurrent processing
                $lockedPayment = Payment::where('id', $payment->id)
                    ->where('status', PaymentStatus::PENDING->value) // Only lock if still pending
                    ->lockForUpdate()
                    ->with(['order'])
                    ->first();

                // Double-check payment is still pending after lock
                if (!$lockedPayment) {
                    Log::info('Payment no longer pending after lock - likely processed concurrently', [
                        'payment_id' => $payment->id,
                        'security' => $securityInfo
                    ]);

                    // Re-fetch to get current status
                    $currentPayment = Payment::find($payment->id);
                    return response()->json([
                        'success' => true,
                        'message' => 'Already processed',
                        'data' => [
                            'payment_id' => $currentPayment->id,
                            'order_number' => $currentPayment->order->order_number,
                            'payment_status' => $currentPayment->status,
                            'order_status' => $currentPayment->order->status
                        ]
                    ], 200);
                }

                // 5. Verify amount with tolerance
                $tolerance = 0.01;
                if (abs($amount - $lockedPayment->amount) > $tolerance) {
                    Log::error('Payment amount mismatch', [
                        'payment_id' => $lockedPayment->id,
                        'expected_amount' => $lockedPayment->amount,
                        'received_amount' => $amount,
                        'difference' => abs($amount - $lockedPayment->amount),
                        'tolerance' => $tolerance,
                        'security' => $securityInfo
                    ]);

                    // Mark payment as failed
                    $lockedPayment->update([
                        'status' => PaymentStatus::FAILED->value,
                        'callback_data' => array_merge($request->all(), ['security' => $securityInfo]),
                        'gateway_response' => [
                            'error' => 'amount_mismatch',
                            'expected' => $lockedPayment->amount,
                            'received' => $amount
                        ]
                    ]);

                    // Track payment failed metrics for amount mismatch
                    $this->metricsService->paymentFailed($lockedPayment, 'Amount mismatch');

                    return response()->json([
                        'success' => false,
                        'message' => 'Amount mismatch'
                    ], 400);
                }

                // 6. Map external status to internal status
                $paymentStatus = PaymentStatusMapper::fromGateway($status);
                $orderStatus = PaymentStatusMapper::toOrderStatus($paymentStatus);
                
                Log::info('Status mapping completed', [
                    'external_status' => $status,
                    'payment_status' => $paymentStatus->value,
                    'order_status' => $orderStatus->value,
                    'payment_id' => $lockedPayment->id
                ]);

                // 7. Prepare callback data with security information
                $callbackData = array_merge($request->all(), ['security' => $securityInfo]);
                
                // 8. Update payment and order based on status
                if ($paymentStatus === PaymentStatus::PAID) {
                    Log::info('Processing successful payment', [
                        'payment_id' => $lockedPayment->id,
                        'order_id' => $lockedPayment->order->id
                    ]);

                    // Update payment
                    $lockedPayment->update([
                        'status' => $paymentStatus->value,
                        'paid_at' => now(),
                        'payment_method' => $paymentSystem,
                        'external_transaction_id' => $transactionId,
                        'callback_data' => $callbackData
                    ]);

                    // Update order
                    $lockedPayment->order->update([
                        'status' => $orderStatus->value,
                        'paid_at' => now(),
                        'payment_reference' => $transactionId
                    ]);

                    Log::info('Payment and order marked as paid', [
                        'payment_id' => $lockedPayment->id,
                        'order_id' => $lockedPayment->order->id,
                        'order_number' => $lockedPayment->order->order_number
                    ]);

                    // Track order paid metrics
                    $this->metricsService->orderPaid($lockedPayment->order);

                } elseif ($paymentStatus === PaymentStatus::FAILED || $paymentStatus === PaymentStatus::EXPIRED) {
                    Log::info('Processing failed/expired payment', [
                        'payment_id' => $lockedPayment->id,
                        'order_id' => $lockedPayment->order->id,
                        'payment_status' => $paymentStatus->value
                    ]);

                    // Update payment
                    $lockedPayment->update([
                        'status' => $paymentStatus->value,
                        'callback_data' => $callbackData,
                        'gateway_response' => [
                            'external_status' => $status,
                            'reason' => $paymentStatus->message()
                        ]
                    ]);

                    // Update order
                    $lockedPayment->order->update([
                        'status' => $orderStatus->value
                    ]);

                    Log::info('Payment and order marked as failed', [
                        'payment_id' => $lockedPayment->id,
                        'order_id' => $lockedPayment->order->id,
                        'final_payment_status' => $paymentStatus->value,
                        'final_order_status' => $orderStatus->value
                    ]);

                    // Track payment failed metrics
                    $failureReason = $status . ' - ' . ($paymentStatus->message() ?? 'Unknown reason');
                    $this->metricsService->paymentFailed($lockedPayment, $failureReason);

                } else { // pending
                    Log::info('Processing pending payment', [
                        'payment_id' => $lockedPayment->id,
                        'payment_status' => $paymentStatus->value
                    ]);

                    // Update payment only
                    $lockedPayment->update([
                        'status' => $paymentStatus->value,
                        'callback_data' => $callbackData
                    ]);

                    // Update order status if needed
                    if ($lockedPayment->order->status === OrderStatus::PENDING->value) {
                        $lockedPayment->order->update([
                            'status' => $orderStatus->value
                        ]);
                    }
                }

                Log::info('Payment callback processed successfully', [
                    'payment_id' => $lockedPayment->id,
                    'order_id' => $lockedPayment->order->id,
                    'final_payment_status' => $lockedPayment->status,
                    'final_order_status' => $lockedPayment->order->status
                ]);

                // 9. Return standard JSON response
                return response()->json([
                    'success' => true,
                    'message' => 'Callback processed successfully',
                    'data' => [
                        'payment_id' => $lockedPayment->id,
                        'order_number' => $lockedPayment->order->order_number,
                        'payment_status' => $lockedPayment->status,
                        'order_status' => $lockedPayment->order->status
                    ]
                ], 200);
            });

        } catch (\Exception $e) {
            Log::error('Payment callback processing failed', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'payload' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => app()->environment('local') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }


    /**
     * Validate E-Billing signature if provided
     */
    private function validateEBillingSignature(Request $request): array
    {
        $signature = $request->header('X-Signature') 
                  ?? $request->header('X-EBilling-Signature')
                  ?? $request->input('signature');

        // If no signature provided, check if we have a shared key configured
        $sharedKey = config('services.ebilling.shared_key');
        
        if (!$sharedKey) {
            Log::info('No E-Billing shared key configured - skipping signature validation');
            return ['valid' => true, 'reason' => 'no_shared_key_configured'];
        }

        if (!$signature) {
            Log::warning('E-Billing shared key configured but no signature provided in callback');
            return ['valid' => false, 'reason' => 'missing_signature'];
        }

        // Generate expected signature using the payload
        $payload = json_encode($request->all(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $expectedSignature = hash_hmac('sha256', $payload, $sharedKey);

        // Compare signatures (timing-safe comparison)
        if (!hash_equals($expectedSignature, $signature)) {
            Log::error('E-Billing signature mismatch', [
                'expected' => $expectedSignature,
                'received' => $signature,
                'payload_length' => strlen($payload)
            ]);
            return ['valid' => false, 'reason' => 'signature_mismatch'];
        }

        Log::info('E-Billing signature validation successful');
        return ['valid' => true, 'reason' => 'signature_valid'];
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
            ->with(['order'])
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

        // Get payment and order status enums
        $paymentStatus = PaymentStatus::from($payment->status);
        $orderStatus = $payment->order ? OrderStatus::from($payment->order->status) : null;

        // Get contextual status message
        $statusMessage = PaymentStatusMapper::getStatusMessage($paymentStatus, $orderStatus);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'status_label' => $paymentStatus->label(),
                'status_message' => $statusMessage,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'reference' => $payment->reference,
                'ebilling_id' => $payment->ebilling_id,
                'payment_method' => $payment->payment_method,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
                'order' => $payment->order ? [
                    'id' => $payment->order->id,
                    'order_number' => $payment->order->order_number,
                    'status' => $payment->order->status,
                    'status_label' => $orderStatus->label(),
                    'status_message' => $orderStatus->message(),
                    'type' => $payment->order->type,
                    'total_amount' => $payment->order->total_amount,
                    'paid_at' => $payment->order->paid_at,
                    'fulfilled_at' => $payment->order->fulfilled_at,
                ] : null,
                'is_final' => $paymentStatus->isFinal(),
                'is_successful' => $paymentStatus->isSuccessful(),
                'error_message' => $payment->gateway_response['failure_reason'] ?? null
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

                // Créer d'abord l'ordre
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'user_id' => $user->id,
                    'type' => Order::TYPE_LOTTERY,
                    'lottery_id' => $lottery->id,
                    'total_amount' => $amount,
                    'currency' => 'XAF',
                    'status' => Order::STATUS_PENDING,
                ]);

                // Créer la transaction liée à l'ordre
                $transaction = Transaction::create([
                    'transaction_id' => 'TXN_' . strtoupper(Str::random(12)),
                    'user_id' => $user->id,
                    'order_id' => $order->id,
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

                // Créer d'abord l'ordre
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'user_id' => $user->id,
                    'type' => Order::TYPE_DIRECT,
                    'product_id' => $product->id,
                    'total_amount' => $product->price,
                    'currency' => 'XAF',
                    'status' => Order::STATUS_PENDING,
                ]);

                // Créer la transaction liée à l'ordre
                $transaction = Transaction::create([
                    'transaction_id' => 'TXN_' . strtoupper(Str::random(12)),
                    'user_id' => $user->id,
                    'order_id' => $order->id,
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
                'message' => 'Commande créée avec succès',
                'redirect_to_payment' => true,
                'data' => [
                    'order_number' => $order->order_number,
                    'amount' => $order->total_amount,
                    'reference' => $order->order_number,
                    'type' => $order->type
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
