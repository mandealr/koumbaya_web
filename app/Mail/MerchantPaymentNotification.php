<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MerchantPaymentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment)
    {
        Log::info('MAIL :: MerchantPaymentNotification mailable instantiated', [
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'amount' => $payment->amount
        ]);

        $this->payment = $payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        Log::info('MAIL :: MerchantPaymentNotification envelope created', [
            'payment_id' => $this->payment->id,
            'subject' => 'Nouveau paiement reçu - Koumbaya'
        ]);

        return new Envelope(
            subject: 'Nouveau paiement reçu - Koumbaya',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('MAIL :: MerchantPaymentNotification content definition created', [
            'payment_id' => $this->payment->id,
            'view' => 'emails.merchant-payment-notification'
        ]);

        return new Content(
            view: 'emails.merchant-payment-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
