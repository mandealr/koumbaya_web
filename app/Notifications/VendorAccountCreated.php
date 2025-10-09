<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorAccountCreated extends Notification
{

    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        // Générer l'URL de reset password
        // Utiliser l'URL du frontend avec le token
        $frontendUrl = config('app.frontend_url', config('app.url'));
        $resetUrl = "{$frontendUrl}/reset-password?token={$this->token}&email=" . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Bienvenue sur Koumbaya - Créez votre mot de passe')
            ->greeting('Bonjour ' . $notifiable->first_name . ' ' . $notifiable->last_name . ' !')
            ->line('Votre compte vendeur professionnel a été créé avec succès sur Koumbaya.')
            ->line('Pour accéder à votre compte, vous devez d\'abord créer votre mot de passe.')
            ->action('Créer mon mot de passe', $resetUrl)
            ->line('Ce lien expirera dans 60 minutes.')
            ->line('Si vous n\'avez pas demandé la création de ce compte, veuillez ignorer cet email.')
            ->salutation('Cordialement, L\'équipe Koumbaya');
    }
}
