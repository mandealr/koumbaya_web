<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $previousStatus;
    public string $newStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $previousStatus, string $newStatus)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusLabels = [
            'paid' => 'Payée',
            'shipping' => 'En cours de livraison',
            'fulfilled' => 'Livrée',
            'cancelled' => 'Annulée'
        ];

        $subject = sprintf(
            '📦 Commande %s - %s',
            $this->order->order_number,
            $statusLabels[$this->newStatus] ?? 'Mise à jour'
        );

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'noreply@koumbaya.cm'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status-changed',
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
