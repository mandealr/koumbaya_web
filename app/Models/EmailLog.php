<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'email_type',
        'recipient_email', 
        'user_id',
        'subject',
        'status',
        'error_message',
        'queued_at',
        'sent_at',
        'failed_at',
        'metadata',
        'message_id'
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime', 
            'failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('email_type', $type);
    }

    // Helper methods
    public static function logEmail(
        string $emailType,
        string $recipientEmail,
        string $subject,
        ?int $userId = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'email_type' => $emailType,
            'recipient_email' => $recipientEmail,
            'user_id' => $userId,
            'subject' => $subject,
            'status' => 'queued',
            'queued_at' => now(),
            'metadata' => $metadata
        ]);
    }

    public function markAsSent(?string $messageId = null): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'message_id' => $messageId
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage
        ]);
    }
}
