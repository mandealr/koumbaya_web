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
        // Use existing order reference if available, otherwise generate one
        $reference = $data->order_reference ?? $data->reference ?? self::generateReference();
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
     * Initiate E-Billing with existing payment (update existing payment instead of creating new one)
     */
    public static function initiateWithExistingPayment($type, $data, $existingPayment)
    {
        Log::info('E-BILLING :: Initiating with existing payment', [
            'type' => $type,
            'payment_id' => $existingPayment->id,
            'payment_reference' => $existingPayment->reference,
            'user_id' => $data->user->id
        ]);

        // Use existing payment's reference instead of generating new one
        $data->reference = $existingPayment->reference;
        $fees = 0; // No additional fees for existing payment
        $paymentDataFromSetup = self::setupPaymentData($type, $data, $fees);

        if (!$paymentDataFromSetup) {
            Log::error('E-BILLING :: Error setting up payment data for existing payment', [
                'type' => $type,
                'payment_id' => $existingPayment->id
            ]);
            return false;
        }

        Log::info('E-BILLING :: Payment data prepared for existing payment', [
            'payment_id' => $existingPayment->id,
            'amount' => $paymentDataFromSetup['amount'],
            'description' => $paymentDataFromSetup['short_description']
        ]);

        // Create E-Billing invoice via API
        $bill_id = self::createEBillingInvoice($paymentDataFromSetup);

        if ($bill_id) {
            Log::info('E-BILLING :: Invoice created for existing payment', [
                'payment_id' => $existingPayment->id,
                'bill_id' => $bill_id
            ]);

            // Update existing payment with eBilling information but keep the original reference
            $existingPayment->update([
                'ebilling_id' => $bill_id,
                'meta' => array_merge($existingPayment->meta ?? [], [
                    'ebilling_id' => $bill_id,
                    'description' => $paymentDataFromSetup['short_description'],
                    'customer_name' => $paymentDataFromSetup['payer_name'],
                    'customer_phone' => $paymentDataFromSetup['payer_msisdn'],
                    'customer_email' => $paymentDataFromSetup['payer_email'],
                    'payment_gateway' => 'ebilling',
                    'callback_url' => $paymentDataFromSetup['callback_url'] ?? null,
                    'type' => $type,
                    'expiry_period' => $paymentDataFromSetup['expiry_period']
                ])
            ]);

            Log::info('E-BILLING :: Payment updated successfully with bill_id', [
                'payment_id' => $existingPayment->id,
                'ebilling_id' => $bill_id,
                'reference' => $existingPayment->reference
            ]);
        } else {
            Log::error('E-BILLING :: Failed to create invoice for existing payment', [
                'payment_id' => $existingPayment->id
            ]);
        }

        return $bill_id;
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
            'ebilling_id' => $billId, // Stocker ebilling_id dans la colonne directe
            'meta' => [
                'ebilling_id' => $billId, // Garder aussi dans meta pour compatibilité
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
        Log::info('E-BILLING :: Initiating USSD push', [
            'bill_id' => $billId,
            'payment_system' => $paymentSystemName,
            'payer_msisdn' => $payerMsisdn,
            'type' => $type
        ]);

        $username = config('services.ebilling.username');
        $sharedKey = config('services.ebilling.shared_key');

        $auth = $username . ':' . $sharedKey;

        $base64 = base64_encode($auth);

        $url = env('URL_EB') . 'e_bills/' . $billId . '/ussd_push';

        try {
            Log::info('E-BILLING :: Sending USSD push request', [
                'url' => $url,
                'payment_system_name' => $paymentSystemName,
                'payer_msisdn' => $payerMsisdn
            ]);

            $response = Http::withHeaders([
                "Authorization" => "Basic " . $base64
            ])->post($url, [
                "payment_system_name" => $paymentSystemName,
                "payer_msisdn" => $payerMsisdn,
            ]);

            Log::info('E-BILLING :: USSD push response received', [
                'bill_id' => $billId,
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            $responseData = json_decode($response->body());

            if ($responseData) {
                if ($responseData->message == "Accepted") {
                    Log::info('E-BILLING :: USSD push accepted', [
                        'bill_id' => $billId,
                        'payer_msisdn' => $payerMsisdn,
                        'response' => $responseData
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Push USSD envoyé avec succès. Gardez votre téléphone à portée de main pour valider la transaction avec votre code PIN secret.',
                        'data' => $responseData
                    ];
                } else {
                    Log::warning('E-BILLING :: USSD push not accepted', [
                        'bill_id' => $billId,
                        'message' => $responseData->message ?? 'Unknown',
                        'response' => $responseData
                    ]);

                    return [
                        'success' => false,
                        'message' => $responseData->message ?? 'Push USSD échoué'
                    ];
                }
            } else {
                Log::error('E-BILLING :: Empty response from USSD push', [
                    'bill_id' => $billId,
                    'response_body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Échec du Push USSD.'
                ];
            }
        } catch (\Exception $e) {
            Log::error('E-BILLING :: USSD push exception', [
                'bill_id' => $billId,
                'payment_system' => $paymentSystemName,
                'payer_msisdn' => $payerMsisdn,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
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
        Log::info('E-BILLING :: Requesting KYC information', [
            'operator' => $operator,
            'phone' => $phone
        ]);

        $username = config('services.ebilling.username');
        $sharedKey = config('services.ebilling.shared_key');
        $ebillingUrl = config('services.ebilling.url');

        $url = $ebillingUrl . 'kyc?payment_system_name=' . $operator . '&msisdn=' . $phone;

        try {
            Log::info('E-BILLING :: Sending KYC request', [
                'url' => $url,
                'operator' => $operator
            ]);

            $response = Http::withBasicAuth($username, $sharedKey)->get($url);

            Log::info('E-BILLING :: KYC response received', [
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['key_data'])) {
                    Log::info('E-BILLING :: KYC data retrieved successfully', [
                        'operator' => $operator,
                        'phone' => $phone,
                        'has_data' => true
                    ]);

                    return ['success' => true, 'data' => $responseData['key_data']];
                }
            }

            Log::warning('E-BILLING :: KYC request failed', [
                'operator' => $operator,
                'phone' => $phone,
                'status_code' => $response->status(),
                'response' => $response->body()
            ]);

            return ['success' => false, 'message' => 'KYC failed'];
        } catch (\Exception $e) {
            Log::error('E-BILLING :: KYC exception', [
                'operator' => $operator,
                'phone' => $phone,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
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

        // Try to find payment by reference first, then by ebilling_id (both column and meta)
        $payment = Payment::where('reference', $notificationData['reference'])
            ->orWhere('ebilling_id', $notificationData['billingid'])
            ->orWhereJsonContains('meta->ebilling_id', $notificationData['billingid'])
            ->first();

        if (!$payment) {
            Log::warning('E-BILLING :: Payment not found', [
                'reference' => $notificationData['reference'],
                'ebilling_id' => $notificationData['billingid'] ?? null
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

            // Transaction record is already created as Payment, no need for duplicate

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
     * Add transaction to user's history - DEPRECATED
     * Payment record already serves as transaction record
     */
    private static function addTransaction(Payment $payment)
    {
        // No longer needed - Payment table serves as transaction history
        // This method was creating duplicate records
        return;
    }

    /**
     * Generate unique reference
     */
    private static function generateReference($length = 10)
    {
        return 'TXN-' . time() . '-' . strtoupper(Str::random(6));
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
            if ($payment->user && $payment->user->email) {
                Mail::to($payment->user->email)->send(new \App\Mail\PaymentConfirmation($payment));

                Log::info('E-BILLING :: Customer email sent successfully', [
                    'payment_id' => $payment->id,
                    'customer_email' => $payment->user->email
                ]);
            }

            // Send notification to merchant if product has a merchant
            if ($payment->order && $payment->order->product && $payment->order->product->merchant_id) {
                $merchant = \App\Models\User::find($payment->order->product->merchant_id);

                if ($merchant && $merchant->email) {
                    Mail::to($merchant->email)->send(new \App\Mail\MerchantPaymentNotification($payment));

                    Log::info('E-BILLING :: Merchant email sent successfully', [
                        'payment_id' => $payment->id,
                        'merchant_id' => $merchant->id,
                        'merchant_email' => $merchant->email
                    ]);
                }
            }

            // Also send to admin email if configured
            if (config('mail.admin_email')) {
                Mail::to(config('mail.admin_email'))->send(new \App\Mail\MerchantPaymentNotification($payment));

                Log::info('E-BILLING :: Admin email sent successfully', [
                    'payment_id' => $payment->id,
                    'admin_email' => config('mail.admin_email')
                ]);
            }
        } catch (\Exception $e) {
            Log::error('E-BILLING :: Failed to send email notifications', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
