<?php

namespace App\Mail\Auth;

use App\Mail\BaseKoumbayaMail;
use App\Models\User;

class ResetPasswordMail extends BaseKoumbayaMail
{
    public $user;
    public $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $resetUrl)
    {
        $this->user = $user;
        $this->resetUrl = $resetUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return parent::build()
                    ->subject('Réinitialisation de votre mot de passe Koumbaya')
                    ->view('emails.auth.reset-password')
                    ->with([
                        'subject' => 'Réinitialisation de mot de passe',
                        'user' => $this->user,
                        'resetUrl' => $this->resetUrl,
                    ])
                    ->addTracking('auth')
                    ->setHighPriority();
    }
}