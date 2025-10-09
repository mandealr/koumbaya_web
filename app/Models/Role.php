<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
        'mutable',
        'user_type_id',
        'merchant_id',
        'user_id'
    ];

    protected $casts = [
        'active' => 'boolean',
        'mutable' => 'boolean',
    ];

    /**
     * Relations
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    public function privileges()
    {
        return $this->belongsToMany(Privilege::class, 'role_privileges', 'role_id', 'privilege_id');
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    /**
     * Scopes
     */
    public function scopeForUserType($query, $userTypeId)
    {
        return $query->where('user_type_id', $userTypeId);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Helpers
     */
    public function isAdmin(): bool
    {
        return $this->userType && $this->userType->code === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->userType && $this->userType->code === 'customer';
    }
}
