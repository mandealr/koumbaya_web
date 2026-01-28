<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $merchantName;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $merchantName = '')
    {
        $this->order = $order;
        $this->merchantName = $merchantName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📦 Rappel : Confirmez la réception de votre commande #{$this->order->order_number}",
            from: config('mail.from.address', 'noreply@koumbaya.cm'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.delivery-reminder',
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
