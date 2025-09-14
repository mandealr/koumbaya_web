<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $purpose;
    
    /**
     * Create a new message instance.
     */
    public function __construct(string $code, string $purpose = 'registration')
    {
        $this->code = $code;
        $this->purpose = $purpose;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getEmailSubject($this->purpose);
        
        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'noreply@koumbaya.com'),
            replyTo: 'support@koumbaya.com'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.otp-verification',
            with: [
                'code' => $this->code,
                'purpose' => $this->purpose
            ]
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
    
    /**
     * Obtenir le sujet de l'email selon le purpose
     */
    private function getEmailSubject(string $purpose): string
    {
        return match($purpose) {
            'registration' => 'Koumbaya - Code de vérification d\'inscription',
            'login' => 'Koumbaya - Code de connexion sécurisée', 
            'payment' => 'Koumbaya - Code de confirmation de paiement',
            'password_reset' => 'Koumbaya - Code de réinitialisation',
            default => 'Koumbaya - Code de vérification'
        };
    }
}