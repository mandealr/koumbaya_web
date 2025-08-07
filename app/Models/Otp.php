<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Otp extends Model
{
    use HasFactory;

    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';

    const PURPOSE_REGISTRATION = 'registration';
    const PURPOSE_PASSWORD_RESET = 'password_reset';
    const PURPOSE_LOGIN = 'login';
    const PURPOSE_PAYMENT = 'payment';

    protected $fillable = [
        'identifier',
        'code',
        'type',
        'purpose',
        'expires_at',
        'is_used',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Générer un code OTP
     */
    public static function generate($identifier, $type, $purpose, $expirationMinutes = 5)
    {
        // Nettoyer les anciens codes non utilisés pour cet identifiant
        self::where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->delete();

        // Générer un code à 6 chiffres
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'identifier' => $identifier,
            'code' => $code,
            'type' => $type,
            'purpose' => $purpose,
            'expires_at' => Carbon::now()->addMinutes($expirationMinutes),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Vérifier un code OTP
     */
    public static function verify($identifier, $code, $purpose)
    {
        $otp = self::where('identifier', $identifier)
                   ->where('code', $code)
                   ->where('purpose', $purpose)
                   ->where('is_used', false)
                   ->where('expires_at', '>', Carbon::now())
                   ->first();

        if ($otp) {
            $otp->markAsUsed();
            return true;
        }

        return false;
    }

    /**
     * Marquer comme utilisé
     */
    public function markAsUsed()
    {
        $this->is_used = true;
        $this->save();
    }

    /**
     * Vérifier si le code est expiré
     */
    public function isExpired()
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Vérifier si le code est valide
     */
    public function isValid()
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Nettoyer les anciens codes expirés
     */
    public static function cleanup()
    {
        return self::where('expires_at', '<', Carbon::now()->subHours(24))->delete();
    }

    /**
     * Formater l'identifiant pour l'affichage
     */
    public function getMaskedIdentifierAttribute()
    {
        if ($this->type === self::TYPE_EMAIL) {
            $parts = explode('@', $this->identifier);
            if (count($parts) === 2) {
                $username = $parts[0];
                $domain = $parts[1];
                $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
                return $maskedUsername . '@' . $domain;
            }
        } elseif ($this->type === self::TYPE_SMS) {
            $length = strlen($this->identifier);
            if ($length > 6) {
                return substr($this->identifier, 0, 3) . str_repeat('*', $length - 6) . substr($this->identifier, -3);
            }
        }

        return $this->identifier;
    }

    /**
     * Scope pour les codes valides
     */
    public function scopeValid($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope pour un purpose spécifique
     */
    public function scopeForPurpose($query, $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    /**
     * Scope pour un identifiant spécifique
     */
    public function scopeForIdentifier($query, $identifier)
    {
        return $query->where('identifier', $identifier);
    }
}