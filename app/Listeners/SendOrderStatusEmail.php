<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusChangedEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        // Charger les relations nécessaires
        $event->order->load(['user', 'tickets', 'lottery', 'product']);

        if (!$event->order->user || !$event->order->user->email) {
            Log::warning('SendOrderStatusEmail: Pas d\'email utilisateur pour la commande', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
            ]);
            return;
        }

        try {
            Mail::to($event->order->user->email)->send(
                new OrderStatusChangedEmail($event->order, $event->previousStatus, $event->newStatus)
            );

            Log::info('Email changement statut commande envoyé', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'recipient' => $event->order->user->email,
                'previous_status' => $event->previousStatus,
                'new_status' => $event->newStatus,
                'type' => $event->order->type,
                'tickets_count' => $event->order->tickets->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email changement statut commande', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'recipient' => $event->order->user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
