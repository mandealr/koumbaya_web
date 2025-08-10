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
}
