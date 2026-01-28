<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
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
    public function handle(UserRegistered $event): void
    {
        try {
            Mail::to($event->user->email)->send(new WelcomeEmail($event->user));

            Log::info('Email de bienvenue envoyé', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'name' => $event->user->first_name . ' ' . $event->user->last_name,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email de bienvenue', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
