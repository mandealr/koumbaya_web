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

class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment)
    {
        Log::info('MAIL :: PaymentConfirmation mailable instantiated', [
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
        Log::info('MAIL :: PaymentConfirmation envelope created', [
            'payment_id' => $this->payment->id,
            'subject' => 'Confirmation de paiement - Koumbaya'
        ]);

        return new Envelope(
            subject: 'Confirmation de paiement - Koumbaya',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('MAIL :: PaymentConfirmation content definition created', [
            'payment_id' => $this->payment->id,
            'view' => 'emails.payment-confirmation'
        ]);

        return new Content(
            view: 'emails.payment-confirmation',
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
