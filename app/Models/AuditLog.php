<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'tags' => 'array',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Log une action d'authentification
     */
    public static function logAuth($event, $user = null, $metadata = [])
    {
        try {
            // Vérifier si la table existe
            if (!\Schema::hasTable('audit_logs')) {
                \Log::info('AuditLog: Table audit_logs not found, skipping log', [
                    'event' => $event,
                    'user_id' => $user instanceof User ? $user->id : $user
                ]);
                return null;
            }

            $log = [
                'event' => $event,
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'tags' => ['auth'],
                'metadata' => array_merge($metadata, [
                    'platform' => request()->header('X-Platform', 'web'), // web, mobile, api
                    'app_version' => request()->header('X-App-Version'),
                ]),
            ];

            if ($user) {
                $log['user_id'] = $user instanceof User ? $user->id : $user;
                $log['auditable_type'] = User::class;
                $log['auditable_id'] = $user instanceof User ? $user->id : $user;
            }

            return static::create($log);
        } catch (\Exception $e) {
            \Log::warning('AuditLog: Failed to create audit log', [
                'event' => $event,
                'error' => $e->getMessage(),
                'user_id' => $user instanceof User ? $user->id : $user
            ]);
            return null;
        }
    }

    /**
     * Log une erreur d'API
     */
    public static function logApiError($event, $error, $statusCode = null)
    {
        try {
            // Vérifier si la table existe
            if (!\Schema::hasTable('audit_logs')) {
                \Log::info('AuditLog: Table audit_logs not found, skipping error log', [
                    'event' => $event,
                    'error' => $error instanceof \Exception ? $error->getMessage() : $error
                ]);
                return null;
            }

            return static::create([
                'user_id' => auth()->id(),
                'event' => $event,
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'tags' => ['api', 'error'],
                'metadata' => [
                    'error' => $error instanceof \Exception ? $error->getMessage() : $error,
                    'status_code' => $statusCode,
                    'method' => request()->method(),
                    'platform' => request()->header('X-Platform', 'web'),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::warning('AuditLog: Failed to create error log', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}