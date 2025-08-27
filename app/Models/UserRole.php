<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'role_id' => 'integer'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le rôle
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Scope pour récupérer par utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour récupérer par rôle
     */
    public function scopeForRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Vérifier si une association user-role existe
     */
    public static function exists(int $userId, int $roleId): bool
    {
        return static::where('user_id', $userId)
                     ->where('role_id', $roleId)
                     ->exists();
    }

    /**
     * Créer une association user-role si elle n'existe pas
     */
    public static function createIfNotExists(int $userId, int $roleId): self
    {
        return static::firstOrCreate([
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }
}