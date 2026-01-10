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

class AdminPaymentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment)
    {
        Log::info('MAIL :: AdminPaymentNotification mailable instantiated', [
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
        Log::info('MAIL :: AdminPaymentNotification envelope created', [
            'payment_id' => $this->payment->id,
            'subject' => 'Nouveau paiement sur la plateforme - Koumbaya'
        ]);

        return new Envelope(
            subject: 'Nouveau paiement sur la plateforme - Koumbaya',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('MAIL :: AdminPaymentNotification content definition created', [
            'payment_id' => $this->payment->id,
            'view' => 'emails.admin-payment-notification'
        ]);

        return new Content(
            view: 'emails.admin-payment-notification',
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
