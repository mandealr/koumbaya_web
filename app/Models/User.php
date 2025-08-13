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
        'role',
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
        'last_otp_request',
        'country_id',
        'language_id',
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
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function loginHistories()
    {
        return $this->hasMany(UserLoginHistory::class, 'user_id');
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

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'user_id');
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
        return $this->role === 'MERCHANT';
    }
}
