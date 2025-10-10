<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_name',
        'business_email',
        'business_description',
        'tax_id',
        'registration_number',
        'vat_number',
        'address',
        'city',
        'postal_code',
        'country',
        'phone',
        'website',
        'logo_url',
        'company_type',
        'is_verified',
        'verified_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relations
     */

    /**
     * Utilisateurs appartenant à cette entreprise
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Propriétaire principal de l'entreprise (premier utilisateur créé)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Produits de l'entreprise
     */
    public function products()
    {
        return $this->hasManyThrough(Product::class, User::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeIndividual($query)
    {
        return $query->where('company_type', 'individual');
    }

    public function scopeEnterprise($query)
    {
        return $query->where('company_type', 'enterprise');
    }

    /**
     * Helpers
     */
    public function isIndividual(): bool
    {
        return $this->company_type === 'individual';
    }

    public function isEnterprise(): bool
    {
        return $this->company_type === 'enterprise';
    }

    public function verify()
    {
        $this->is_verified = true;
        $this->verified_at = now();
        $this->save();
    }

    public function activate()
    {
        $this->is_active = true;
        $this->save();
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->save();
    }
}
