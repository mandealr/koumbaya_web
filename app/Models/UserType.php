<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code', 
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relation avec les utilisateurs
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relation avec les rôles
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Relation avec les privilèges
     */
    public function privileges(): HasMany
    {
        return $this->hasMany(Privilege::class);
    }

    /**
     * Scope pour récupérer seulement les types actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Vérifier si c'est un type customer
     */
    public function isCustomer(): bool
    {
        return $this->code === 'customer';
    }

    /**
     * Vérifier si c'est un type merchant
     */
    public function isMerchant(): bool
    {
        return $this->code === 'merchant';
    }

    /**
     * Vérifier si c'est un type admin
     */
    public function isAdmin(): bool
    {
        return $this->code === 'admin';
    }

    /**
     * Récupérer un type par son code
     */
    public static function byCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Constantes pour les codes
     */
    public const CUSTOMER = 'customer';
    public const MERCHANT = 'merchant';
    public const ADMIN = 'admin';
}