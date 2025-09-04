<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusChangedEmail;
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
        // Envoyer notification de changement de statut
        $event->order->load('user');
        if ($event->order->user && $event->order->user->email) {
            Mail::to($event->order->user->email)->send(
                new OrderStatusChangedEmail($event->order, $event->previousStatus, $event->newStatus)
            );
        }
    }
}
