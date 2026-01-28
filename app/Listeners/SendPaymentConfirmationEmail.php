<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\PaymentConfirmation;
use App\Mail\MerchantPaymentNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPaymentConfirmationEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event - Send payment confirmation emails when order is paid
     */
    public function handle(OrderStatusChanged $event): void
    {
        // Only send payment confirmation when status changes to 'paid'
        if ($event->newStatus !== 'paid') {
            Log::info('PAYMENT_EMAIL :: Skipping - status is not paid', [
                'order_id' => $event->order->id,
                'new_status' => $event->newStatus
            ]);
            return;
        }

        Log::info('PAYMENT_EMAIL :: Starting payment confirmation email process', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'user_id' => $event->order->user_id,
            'mail_driver' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_from' => config('mail.from.address')
        ]);

        try {
            // Load order with relationships
            $order = $event->order->load(['user', 'product', 'lottery', 'payments']);

            // Get the latest payment
            $payment = $order->payments()->orderBy('created_at', 'desc')->first();

            if (!$payment) {
                Log::warning('PAYMENT_EMAIL :: No payment found for order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);
                return;
            }

            // 1. Send confirmation email to customer
            if ($order->user && $order->user->email) {
                Log::info('PAYMENT_EMAIL :: Attempting to send customer confirmation', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'customer_email' => $order->user->email,
                    'customer_name' => $order->user->first_name . ' ' . $order->user->last_name
                ]);

                try {
                    Mail::to($order->user->email)->send(new PaymentConfirmation($payment));

                    Log::info('PAYMENT_EMAIL :: Customer confirmation email sent successfully', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'customer_email' => $order->user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('PAYMENT_EMAIL :: Failed to send customer confirmation email', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'customer_email' => $order->user->email,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::warning('PAYMENT_EMAIL :: Cannot send customer email - user or email missing', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'has_user' => $order->user ? 'yes' : 'no',
                    'has_email' => $order->user && $order->user->email ? 'yes' : 'no'
                ]);
            }

            // 2. Send notification to merchant if applicable
            // Récupérer le marchand selon le type de commande (tombola ou achat direct)
            $merchant = null;
            $merchantSource = null;

            if ($order->lottery && $order->lottery->product && $order->lottery->product->merchant) {
                // Commande de tombola → marchand via lottery->product->merchant
                $merchant = $order->lottery->product->merchant;
                $merchantSource = 'lottery';
            } elseif ($order->product && $order->product->merchant) {
                // Commande directe → marchand via product->merchant
                $merchant = $order->product->merchant;
                $merchantSource = 'product';
            }

            if ($merchant && $merchant->email) {
                Log::info('PAYMENT_EMAIL :: Attempting to send merchant notification', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'merchant_id' => $merchant->id,
                    'merchant_email' => $merchant->email,
                    'merchant_source' => $merchantSource
                ]);

                try {
                    Mail::to($merchant->email)->send(new MerchantPaymentNotification($payment));

                    Log::info('PAYMENT_EMAIL :: Merchant notification sent successfully', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'merchant_id' => $merchant->id,
                        'merchant_email' => $merchant->email,
                        'merchant_source' => $merchantSource
                    ]);
                } catch (\Exception $e) {
                    Log::error('PAYMENT_EMAIL :: Failed to send merchant notification', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'merchant_email' => $merchant->email,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::info('PAYMENT_EMAIL :: No merchant to notify', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'order_type' => $order->type,
                    'has_lottery' => $order->lottery ? 'yes' : 'no',
                    'has_product' => $order->product ? 'yes' : 'no',
                    'merchant_found' => $merchant ? 'yes' : 'no',
                    'merchant_has_email' => $merchant && $merchant->email ? 'yes' : 'no'
                ]);
            }

            // 3. Send to admin if configured
            $adminEmail = config('mail.admin_email');
            if ($adminEmail) {
                Log::info('PAYMENT_EMAIL :: Attempting to send admin notification', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'admin_email' => $adminEmail
                ]);

                try {
                    Mail::to($adminEmail)->send(new \App\Mail\AdminPaymentNotification($payment));

                    Log::info('PAYMENT_EMAIL :: Admin notification sent successfully', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'admin_email' => $adminEmail
                    ]);
                } catch (\Exception $e) {
                    Log::error('PAYMENT_EMAIL :: Failed to send admin notification', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'admin_email' => $adminEmail,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::info('PAYMENT_EMAIL :: Admin email not configured', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'config_value' => $adminEmail
                ]);
            }

            Log::info('PAYMENT_EMAIL :: Payment confirmation email process completed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'order_number' => $order->order_number
            ]);

        } catch (\Exception $e) {
            Log::error('PAYMENT_EMAIL :: General exception in payment confirmation process', [
                'order_id' => $event->order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
