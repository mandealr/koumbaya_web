<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Privilege extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_type_id'
    ];

    protected $casts = [
        'user_type_id' => 'integer'
    ];

    /**
     * Relations
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_privileges', 'privilege_id', 'role_id');
    }

    /**
     * Relation avec le type d'utilisateur
     */
    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    /**
     * Scope pour récupérer par type d'utilisateur
     */
    public function scopeForUserType($query, $userTypeId)
    {
        return $query->where('user_type_id', $userTypeId);
    }

    /**
     * Scope pour récupérer par nom
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Récupérer un privilège par son nom
     */
    public static function byName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }
}
