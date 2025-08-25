<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'device',
        'platform',
        'browser',
        'ip_address',
        'location',
        'is_current',
        'last_activity'
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'last_activity' => 'datetime'
    ];

    /**
     * Get the user that owns the session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get active sessions for a user
     */
    public static function getActiveSessions(int $userId)
    {
        return static::where('user_id', $userId)
            ->where('last_activity', '>=', now()->subHours(24))
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Mark session as current
     */
    public function markAsCurrent(): void
    {
        // Mark all other sessions as not current
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        // Mark this session as current
        $this->update(['is_current' => true]);
    }

    /**
     * Update last activity
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }
}
