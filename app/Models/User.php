<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'account_type',
        'can_sell',
        'can_buy',
        'business_name',
        'business_email',
        'business_description',
        'city',
        'address',
        'rating',
        'rating_count',
        'facebook_id',
        'google_id',
        'apple_id',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'last_login_date',
        'verified_at',
        'source_ip_address',
        'source_server_info',
        'is_active',
        'mfa_is_active',
        'google2fa_secret',
        'user_type_id',
        'company_id',
        'last_otp_request',
        'country_id',
        'language_id',
        'avatar_url',
        'date_of_birth',
        'gender',
        'bio',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'last_login_date' => 'datetime',
            'verified_at' => 'datetime',
            'last_otp_request' => 'datetime',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'mfa_is_active' => 'boolean',
            'can_sell' => 'boolean',
            'can_buy' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Créer un token d'accès pour l'utilisateur
     */
    public function createAuthToken($tokenName = 'auth-token')
    {
        return $this->createToken($tokenName)->plainTextToken;
    }

    /**
     * Get the full URL for the user's avatar
     */
    public function getAvatarUrlAttribute($value)
    {
        // Si pas d'avatar, retourner null pour utiliser le placeholder par défaut
        if (!$value) {
            return null;
        }

        // Si c'est déjà une URL complète, la retourner telle quelle
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Construire l'URL via l'API avatar pour gérer les erreurs
        $baseUrl = config('app.url', 'https://koumbaya.com');
        
        // Extraire le nom du fichier
        $filename = basename($value);
        
        return $baseUrl . '/api/avatars/' . $filename;
    }

    /**
     * Relations
     */
    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function wallet()
    {
        return $this->hasOne(UserWallet::class, 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'merchant_id');
    }

    public function lotteryTickets()
    {
        return $this->hasMany(LotteryTicket::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function wonLotteries()
    {
        return $this->hasMany(Lottery::class, 'winner_user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function loginHistories()
    {
        return $this->hasMany(UserLoginHistory::class, 'user_id');
    }

    public function loginHistory()
    {
        return $this->hasMany(UserLoginHistory::class, 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Vérifier si l'utilisateur a vérifié son compte
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Vérifier si l'utilisateur peut acheter des produits
     */
    public function canPurchase(): bool
    {
        return $this->isVerified() && $this->is_active && $this->can_buy;
    }

    /**
     * Vérifier si l'utilisateur peut participer aux tombolas
     */
    public function canParticipateInLotteries(): bool
    {
        return $this->canPurchase();
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Accessors
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getIsMerchantAttribute()
    {
        return $this->isMerchant();
    }


    /**
     * Méthodes pour la gestion des rôles
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
    }

    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    public function getPrimaryRole(): ?string
    {
        $role = $this->roles()->orderBy('id')->first();
        return $role ? $role->name : null;
    }

    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Helpers pour les types d'utilisateurs (architecture à deux niveaux)
     * Niveau 1 : UserType (admin, customer)
     * Niveau 2 : Role (superadmin, admin, agent, business_enterprise, business_individual, particulier)
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isManager(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin', 'agent']);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('particulier') || $this->userType?->code === 'customer';
    }

    public function isMerchant(): bool
    {
        return $this->hasRole('business_enterprise') ||
               $this->hasRole('business_individual') ||
               // Support for legacy roles during migration
               $this->hasRole('Business Enterprise') ||
               $this->hasRole('Business Individual') ||
               $this->hasRole('Business');
    }

    /**
     * Vérifie si l'utilisateur est un vendeur particulier/individuel
     */
    public function isIndividualSeller(): bool
    {
        return $this->hasRole('business_individual') || $this->hasRole('Business Individual');
    }

    /**
     * Vérifie si l'utilisateur est un vendeur business/entreprise
     */
    public function isBusinessSeller(): bool
    {
        return $this->hasRole('business_enterprise') || $this->hasRole('Business Enterprise');
    }

    /**
     * Peut personnaliser le nombre de tickets selon son profil
     */
    public function canCustomizeTickets(): bool
    {
        // business_individual : tickets fixes à 500
        if ($this->hasRole('business_individual') || $this->hasRole('Business Individual')) {
            return false;
        }

        // business_enterprise : peut personnaliser
        if ($this->hasRole('business_enterprise') || $this->hasRole('Business Enterprise')) {
            return true;
        }

        // Autres cas
        return false;
    }

    /**
     * Obtient le nombre de tickets fixe selon le profil
     */
    public function getFixedTicketCount(): ?int
    {
        // business_individual : 500 tickets fixes
        if ($this->hasRole('business_individual') || $this->hasRole('Business Individual')) {
            return 500;
        }

        // business_enterprise : pas de limite fixe
        return null;
    }

    /**
     * Obtient le prix minimum de produit selon le profil
     */
    public function getMinProductPrice(): ?int
    {
        // business_individual : prix minimum 100 000 FCFA
        if ($this->hasRole('business_individual') || $this->hasRole('Business Individual')) {
            return 100000;
        }

        // business_enterprise : pas de limite
        return null;
    }
}
