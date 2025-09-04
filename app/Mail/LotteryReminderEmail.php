<?php

namespace App\Mail;

use App\Models\Lottery;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LotteryReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Lottery $lottery;
    public string $reminderType; // '24h' ou '1h'

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Lottery $lottery, string $reminderType = '24h')
    {
        $this->user = $user;
        $this->lottery = $lottery;
        $this->reminderType = $reminderType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->reminderType === '1h' 
            ? '⏰ Dernière heure - Tirage dans 1 heure !'
            : '⏰ Rappel - Tirage demain sur Koumbaya';

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
            view: 'emails.lottery-reminder',
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
