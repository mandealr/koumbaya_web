<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'images',
        'price',
        'ticket_price',
        'min_participants',
        'stock_quantity',
        'category_id',
        'merchant_id',
        'status',
        'is_featured',
    ];

    /**
     * Attributes that should be hidden for arrays.
     */
    protected $hidden = [];

    /**
     * Attributes that should be appended to arrays.
     */
    protected $appends = [
        'main_image',
        'image_url',
        'title', // Alias pour 'name'
        'has_active_lottery',
        'lottery_ends_soon',
        'popularity_score'
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'ticket_price' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Relations
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function lotteries()
    {
        return $this->hasMany(Lottery::class, 'product_id');
    }

    public function activeLottery()
    {
        return $this->hasOne(Lottery::class, 'product_id')
            ->where('status', 'active')
            ->where('end_date', '>', now());
    }

    /**
     * Relation vers la dernière tombola (active ou complétée)
     */
    public function latestLottery()
    {
        return $this->hasOne(Lottery::class, 'product_id')
            ->latest('created_at');
    }

    /**
     * Relation vers toutes les tombolas complétées
     */
    public function completedLotteries()
    {
        return $this->hasMany(Lottery::class, 'product_id')
            ->where('status', 'completed');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'product_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Accessors
     */
    public function getMainImageAttribute()
    {
        return $this->images && is_array($this->images) && count($this->images) > 0 ? $this->images[0] : null;
    }

    /**
     * Alias pour l'URL de l'image principale
     */
    public function getImageUrlAttribute()
    {
        return $this->main_image;
    }

    /**
     * Alias pour le nom (compatibilité app mobile)
     */
    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getIsAvailableAttribute()
    {
        return $this->status === 'active' && $this->stock_quantity > 0;
    }

    /**
     * Business Logic
     */
    public function canCreateLottery()
    {
        return $this->status === 'active' && 
               $this->stock_quantity > 0 && 
               !$this->activeLottery()->exists();
    }

    public function getTotalParticipationAmount()
    {
        return $this->ticket_price * $this->min_participants;
    }

    /**
     * Relation vers tous les tickets de tombola pour ce produit
     */
    public function lotteryTickets()
    {
        return $this->hasManyThrough(
            \App\Models\LotteryTicket::class,
            Lottery::class,
            'product_id',
            'lottery_id'
        );
    }

    /**
     * Vérifier si le produit a une tombola active
     */
    public function getHasActiveLotteryAttribute()
    {
        return $this->activeLottery()->exists();
    }

    /**
     * Vérifier si la tombola se termine bientôt (24h)
     */
    public function getLotteryEndsSoonAttribute()
    {
        $lottery = $this->activeLottery()->first();
        if (!$lottery) return false;
        
        return $lottery->end_date <= now()->addHours(24) && $lottery->end_date > now();
    }

    /**
     * Score de popularité basé sur les tickets vendus
     */
    public function getPopularityScoreAttribute()
    {
        return $this->lotteryTickets()->count();
    }

    /**
     * Obtenir les statistiques du produit
     */
    public function getStatsAttribute()
    {
        return [
            'total_lotteries' => $this->lotteries()->count(),
            'completed_lotteries' => $this->completedLotteries()->count(),
            'total_tickets_sold' => $this->lotteryTickets()->where('status', 'paid')->count(),
            'total_revenue' => $this->transactions()->where('status', 'completed')->sum('amount'),
            'has_active_lottery' => $this->has_active_lottery,
            'popularity_score' => $this->popularity_score
        ];
    }

    /**
     * Méthodes utilitaires pour l'API mobile
     */
    public function toMobileArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title, // Alias
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'ticket_price' => (float) $this->ticket_price,
            'min_participants' => $this->min_participants,
            'images' => $this->images && is_array($this->images) ? $this->images : [],
            'image_url' => $this->image_url,
            'main_image' => $this->main_image,
            'category_id' => $this->category_id,
            'merchant_id' => $this->merchant_id,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_available' => $this->is_available,
            'has_active_lottery' => $this->has_active_lottery,
            'lottery_ends_soon' => $this->lottery_ends_soon,
            'popularity_score' => $this->popularity_score,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
