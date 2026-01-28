<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rated_user_id',
        'rater_user_id',
        'payment_id',
        'order_id',
        'product_id',
        'rating',
        'comment',
        'type',
        'is_verified',
        'helpful_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified' => 'boolean',
        'helpful_count' => 'integer',
    ];

    // ==================== RELATIONS ====================

    /**
     * L'utilisateur qui est noté
     */
    public function ratedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    /**
     * L'utilisateur qui note
     */
    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rater_user_id');
    }

    /**
     * Le paiement associé
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * La commande associée
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Le produit associé
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // ==================== SCOPES ====================

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('rated_user_id', $sellerId)->where('type', 'seller');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePositive($query)
    {
        return $query->where('rating', '>=', 4);
    }

    public function scopeNegative($query)
    {
        return $query->where('rating', '<=', 2);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== ACCESSORS ====================

    public function getStarsAttribute(): string
    {
        return str_repeat('⭐', $this->rating);
    }

    public function getRatingLabelAttribute(): string
    {
        return match ($this->rating) {
            5 => 'Excellent',
            4 => 'Très bien',
            3 => 'Bien',
            2 => 'Moyen',
            1 => 'Mauvais',
            default => 'Non noté',
        };
    }
}
