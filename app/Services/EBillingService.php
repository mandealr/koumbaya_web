<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EBillingService
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Initiate e-billing payment for lottery ticket purchase
     *
     * @param string $type Payment type (lottery_ticket, product_purchase)
     * @param object $data Payment data
     * @param int $fees Additional fees
     * @return string|false Bill ID or false on failure
     */
    public static function initiate($type, $data, $fees = 0)
    {
        $reference = $data->reference ?? self::generateReference();

        Log::info('E-BILLING :: Initiating payment', [
            'type' => $type,
            'reference' => $reference
        ]);

        // Check if payment already exists
        $existingPayment = Payment::where('reference', $reference)->first();
        if ($existingPayment && $existingPayment->status === self::STATUS_PAID) {
            Log::info('E-BILLING :: Payment already exists and is paid', [
                'reference' => $reference
            ]);
            return $existingPayment->billing_id;
        }

        try {
            // Setup payment attributes based on type
            $paymentData = self::setupPaymentData($type, $data, $fees);

            if (!$paymentData) {
                Log::error('E-BILLING :: Failed to setup payment data');
                return false;
            }

            // Create e-billing invoice
            $billId = self::createEBillingInvoice($paymentData);

            if (!$billId) {
                Log::error('E-BILLING :: Failed to create e-billing invoice');
                return false;
            }

            // Save payment to database
            self::savePayment($type, $data, $paymentData, $billId);

            Log::info('E-BILLING :: Payment initiated successfully', [
                'bill_id' => $billId,
                'reference' => $paymentData['external_reference']
            ]);

            return $billId;
        } catch (\Exception $e) {
            Log::error('E-BILLING :: Error initiating payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Setup payment data based on type
     */
    private static function setupPaymentData($type, $data, $fees)
    {
        $reference = self::generateReference();
        $expiry_period = 60; // 60 minutes timeout

        switch ($type) {
            case 'lottery_ticket':
                return [
                    'payer_email' => $data->user->email,
                    'payer_msisdn' => $data->phone ?? $data->user->phone ?? '074808000',
                    'amount' => $data->amount + $fees,
                    'short_description' => "Achat de {$data->quantity} ticket(s) pour la tombola #{$data->lottery_id}",
                    'external_reference' => $reference,
                    'payer_name' => trim(($data->user->first_name ?? '') . ' ' . ($data->user->last_name ?? '')) ?: 'Client Koumbaya',
                    'expiry_period' => $expiry_period,
                    'callback_url' => url('/api/payments/callback/lottery_ticket/' . $data->lottery_id)
                ];

            case 'product_purchase':
                return [
                    'payer_email' => $data->user->email,
                    'payer_msisdn' => $data->user->phone ?? '074808000',
                    'amount' => $data->product->price + $fees,
                    'short_description' => "Achat direct - {$data->product->title}",
                    'external_reference' => $reference,
                    'payer_name' => trim(($data->user->first_name ?? '') . ' ' . ($data->user->last_name ?? '')) ?: 'Client Koumbaya',
                    'expiry_period' => $expiry_period,
                    'callback_url' => url('/api/payments/callback/product_purchase/' . $data->product->id)
                ];

            default:
                return null;
        }
    }

    /**
     * Create e-billing invoice via API
     */
    private static function createEBillingInvoice($paymentData)
    {
        $username = config('services.ebilling.username');
        $sharedKey = config('services.ebilling.shared_key');
        $serverUrl = config('services.ebilling.server_url');

        if (!$username || !$sharedKey || !$serverUrl) {
            Log::error('E-BILLING :: Missing configuration');
            return false;
        }

        $globalArray = [
            'payer_email' => $paymentData['payer_email'],
            'payer_msisdn' => $paymentData['payer_msisdn'],
            'amount' => (int) $paymentData['amount'], // S'assurer que c'est un entier
            'short_description' => $paymentData['short_description'],
            'external_reference' => $paymentData['external_reference'],
            'payer_name' => $paymentData['payer_name'],
            'expiry_period' => (int) $paymentData['expiry_period'], // S'assurer que c'est un entier
            'callback_url' => $paymentData['callback_url'] ?? null
        ];

        try {
            Log::info('E-BILLING :: Sending request to API', [
                'url' => $serverUrl,
                'username' => $username,
                'data' => $globalArray
            ]);

            // Utiliser cURL comme dans l'exemple original
            $content = json_encode($globalArray);
            $curl = curl_init($serverUrl);
            curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $sharedKey);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
            $json_response = curl_exec($curl);

            // Get status code
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            Log::info('E-BILLING :: API response received', [
                'status' => $status,
                'body' => $json_response,
                'curl_error' => curl_error($curl),
                'curl_errno' => curl_errno($curl)
            ]);

            // Check status comme dans l'exemple original
            if ($status < 200 || $status > 299) {
                Log::error('E-BILLING :: API call failed', [
                    'status' => $status,
                    'response' => $json_response,
                    'curl_error' => curl_error($curl),
                    'curl_errno' => curl_errno($curl),
                    'request_data' => $globalArray
                ]);
                curl_close($curl);
                return false;
            }

            curl_close($curl);

            // Get response in JSON format
            $response = json_decode($json_response, true);
            Log::info('E-BILLING :: Parsed response data', ['response_data' => $response]);

            // Get unique transaction id comme dans l'exemple
            $bill_id = $response['e_bill']['bill_id'] ?? null;

            if (!$bill_id) {
                Log::error('E-BILLING :: Could not extract bill_id from response', ['response' => $response]);
                return false;
            }

            return $bill_id;
        } catch (\Exception $e) {
            Log::error('E-BILLING :: Exception during API call', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Save payment to database
     */
    private static function savePayment($type, $data, $paymentDataFromSetup, $billId)
    {
        $paymentToSave = [
            'reference' => $paymentDataFromSetup['external_reference'],
            'user_id' => $data->user->id,
            'order_id' => $data->order_id ?? null,
            'amount' => $paymentDataFromSetup['amount'],
            'status' => self::STATUS_PENDING,
            'meta' => [
                'ebilling_id' => $billId,
                'description' => $paymentDataFromSetup['short_description'],
                'customer_name' => $paymentDataFromSetup['payer_name'],
                'customer_phone' => $paymentDataFromSetup['payer_msisdn'],
                'customer_email' => $paymentDataFromSetup['payer_email'],
                'payment_gateway' => 'ebilling',
                'callback_url' => $paymentDataFromSetup['callback_url'] ?? null
            ]
        ];

        // Ajouter les données spécifiques selon le type
        $paymentToSave['meta']['type'] = $type;
        $paymentToSave['meta']['expiry_period'] = $paymentDataFromSetup['expiry_period'];

        switch ($type) {
            case 'lottery_ticket':
                $paymentToSave['meta']['lottery_id'] = $data->lottery_id;
                $paymentToSave['meta']['quantity'] = $data->quantity;
                $paymentToSave['lottery_id'] = $data->lottery_id;
                break;

            case 'product_purchase':
                $paymentToSave['meta']['product_id'] = $data->product->id;
                $paymentToSave['product_id'] = $data->product->id;
                break;
        }

        Payment::create($paymentToSave);
    }

    /**
     * Push USSD to user's phone
     */
    public static function pushUssd($billId, $paymentSystemName, $payerMsisdn, $type = 'payment')
    {

        $username = config('services.ebilling.username');
        $sharedKey = config('services.ebilling.shared_key');

        $auth = $username . ':' . $sharedKey;

        $base64 = base64_encode($auth);

        try {
            $response = Http::withHeaders([
                "Authorization" => "Basic " . $base64
            ])->post(env('URL_EB') . 'e_bills/' . $billId . '/ussd_push', [
                "payment_system_name" => $paymentSystemName,
                "payer_msisdn" => $payerMsisdn,
            ]);

            $responseData = json_decode($response->body());

            if ($responseData) {
                if ($responseData->message == "Accepted") {
                    return [
                        'success' => true,
                        'message' => 'Push USSD envoyé avec succès. Gardez votre téléphone à portée de main pour valider la transaction avec votre code PIN secret.',
                        'data' => $responseData
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $responseData->message ?? 'Push USSD échoué'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Échec du Push USSD.'
                ];
            }
        } catch (\Exception $e) {
            Log::error('E-BILLING :: USSD push failed', [
                'error' => $e->getMessage(),
                'bill_id' => $billId
            ]);
            return [
                'success' => false,
                'message' => 'Push USSD échoué'
            ];
        }
    }

    /**
     * Get KYC information
     */
    public static function getKyc($operator, $phone)
    {
        $username = config('services.ebilling.username');
        $sharedKey = config('services.ebilling.shared_key');
        $ebillingUrl = config('services.ebilling.url');

        $url = $ebillingUrl . 'kyc?payment_system_name=' . $operator . '&msisdn=' . $phone;

        try {
            $response = Http::withBasicAuth($username, $sharedKey)->get($url);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['key_data'])) {
                    return ['success' => true, 'data' => $responseData['key_data']];
                }
            }

            return ['success' => false, 'message' => 'KYC failed'];
        } catch (\Exception $e) {
            Log::error('E-BILLING :: KYC failed', [
                'error' => $e->getMessage(),
                'operator' => $operator,
                'phone' => $phone
            ]);
            return ['success' => false, 'message' => 'KYC failed'];
        }
    }

    /**
     * Process payment notification from e-billing
     */
    public static function processNotification($notificationData)
    {
        Log::info('E-BILLING :: Processing payment notification', [
            'payload' => $notificationData
        ]);

        if (!isset($notificationData['reference'])) {
            Log::warning('E-BILLING :: Missing reference in notification');
            return ['status' => 401, 'message' => 'Missing reference'];
        }

        $payment = Payment::where('reference', $notificationData['reference'])->first();

        if (!$payment) {
            Log::warning('E-BILLING :: Payment not found', [
                'reference' => $notificationData['reference']
            ]);
            return ['status' => 402, 'message' => 'Payment not found'];
        }

        if ($payment->status === self::STATUS_PAID) {
            Log::info('E-BILLING :: Payment already processed', [
                'reference' => $payment->reference
            ]);
            return ['status' => 204, 'message' => 'Already processed'];
        }

        // Update payment status
        $payment->status = self::STATUS_PAID;
        $payment->transaction_id = $notificationData['transactionid'] ?? $payment->transaction_id;
        $payment->payment_method = $notificationData['paymentsystem'] ?? null;
        $payment->amount = $notificationData['amount'] ?? $payment->amount;
        $payment->paid_at = now();

        if ($payment->save()) {
            Log::info('E-BILLING :: Payment updated successfully', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference
            ]);

            // Process post-payment actions
            self::afterPayment($payment);

            return ['status' => 200, 'message' => 'Payment processed successfully'];
        }

        Log::error('E-BILLING :: Failed to update payment', [
            'payment_id' => $payment->id
        ]);
        return ['status' => 500, 'message' => 'Failed to update payment'];
    }

    /**
     * Process actions after successful payment
     */
    private static function afterPayment(Payment $payment)
    {
        try {
            Log::info('E-BILLING :: Processing after payment', [
                'payment_id' => $payment->id,
                'type' => $payment->type
            ]);

            switch ($payment->type) {
                case 'lottery_ticket':
                    self::processLotteryTicketPayment($payment);
                    break;

                case 'product_purchase':
                    self::processProductPurchasePayment($payment);
                    break;
            }

            // Add transaction to user wallet
            self::addTransaction($payment);

            // Send email notifications
            self::sendPaymentNotifications($payment);
        } catch (\Exception $e) {
            Log::error('E-BILLING :: Error in after payment processing', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process lottery ticket payment
     */
    private static function processLotteryTicketPayment(Payment $payment)
    {
        // Récupérer la lottery_id depuis gateway_config
        $gatewayConfig = $payment->gateway_config;
        $lotteryId = $gatewayConfig['lottery_id'] ?? null;

        if (!$lotteryId) {
            Log::error('E-BILLING :: Lottery ID not found in gateway config');
            return;
        }

        $lottery = Lottery::find($lotteryId);

        if (!$lottery) {
            Log::error('E-BILLING :: Lottery not found for ticket payment', [
                'lottery_id' => $lotteryId
            ]);
            return;
        }

        // Calculate number of tickets to create based on amount
        $ticketPrice = $lottery->ticket_price;
        $ticketQuantity = floor($payment->amount / $ticketPrice);

        // Create lottery tickets
        for ($i = 0; $i < $ticketQuantity; $i++) {
            LotteryTicket::create([
                'lottery_id' => $lottery->id,
                'user_id' => $payment->user_id,
                'ticket_number' => self::generateTicketNumber(),
                'price' => $lottery->ticket_price,
                'currency' => 'XAF',
                'status' => 'reserved',
                'purchased_at' => now(),
                'payment_id' => $payment->id
            ]);
        }

        Log::info('E-BILLING :: Created lottery tickets', [
            'lottery_id' => $lottery->id,
            'user_id' => $payment->user_id,
            'quantity' => $ticketQuantity
        ]);
    }

    /**
     * Process direct product purchase payment
     */
    private static function processProductPurchasePayment(Payment $payment)
    {
        // Récupérer le product_id depuis gateway_config
        $gatewayConfig = $payment->gateway_config;
        $productId = $gatewayConfig['product_id'] ?? null;

        if (!$productId) {
            Log::error('E-BILLING :: Product ID not found in gateway config');
            return;
        }

        $lottery = Lottery::where('product_id', $productId)
            ->where('status', 'active')
            ->first();

        if ($lottery) {
            // Cancel the lottery and refund participants
            $lottery->status = 'cancelled_direct_purchase';
            $lottery->save();

            // Refund all lottery participants
            $tickets = LotteryTicket::where('lottery_id', $lottery->id)->get();

            foreach ($tickets as $ticket) {
                $userWallet = UserWallet::firstOrCreate(['user_id' => $ticket->user_id]);
                $userWallet->balance += $lottery->ticket_price;
                $userWallet->save();

                // Create refund transaction
                Payment::create([
                    'user_id' => $ticket->user_id,
                    'type' => 'refund',
                    'amount' => $lottery->ticket_price,
                    'description' => "Remboursement tombola #{$lottery->id} - Achat direct",
                    'status' => 'completed',
                    'reference' => 'REFUND_' . Str::upper(Str::random(10))
                ]);
            }

            Log::info('E-BILLING :: Cancelled lottery and refunded participants', [
                'lottery_id' => $lottery->id,
                'refunded_tickets' => $tickets->count()
            ]);
        }

        // Mark product as sold
        // Additional logic for product delivery can be added here

        Log::info('E-BILLING :: Processed direct product purchase', [
            'product_id' => $payment->product_id,
            'user_id' => $payment->user_id
        ]);
    }

    /**
     * Add transaction to user's history
     */
    private static function addTransaction(Payment $payment)
    {
        Payment::create([
            'user_id' => $payment->user_id,
            'payment_id' => $payment->id,
            'type' => $payment->type,
            'amount' => $payment->amount,
            'description' => $payment->description,
            'status' => 'completed',
            'reference' => $payment->reference
        ]);
    }

    /**
     * Generate unique reference
     */
    private static function generateReference($length = 10)
    {
        return strtoupper(Str::random($length));
    }

    /**
     * Generate unique ticket number
     */
    private static function generateTicketNumber()
    {
        return 'TKT_' . strtoupper(Str::random(8));
    }

    /**
     * Send email notifications for successful payments
     */
    private static function sendPaymentNotifications(Payment $payment)
    {
        try {
            // Send notification to customer
            Mail::to($payment->user->email)->send(new \App\Mail\PaymentConfirmation($payment));

            // Send notification to merchant (admin for now)
            if (config('mail.admin_email')) {
                Mail::to(config('mail.admin_email'))->send(new \App\Mail\MerchantPaymentNotification($payment));
            }

            Log::info('E-BILLING :: Email notifications sent', [
                'payment_id' => $payment->id,
                'customer_email' => $payment->user->email
            ]);
        } catch (\Exception $e) {
            Log::error('E-BILLING :: Failed to send email notifications', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
