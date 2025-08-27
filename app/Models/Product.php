<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\TicketPriceCalculator;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'currency',
        'stock_quantity',
        'category_id',
        'merchant_id',
        'sale_mode',
        'is_featured',
        'is_active',
        'views_count',
        'meta',
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

    /**
     * Default attributes values
     */
    protected $attributes = [
        'sale_mode' => 'direct',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'meta' => 'array',
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
        return $query->where('is_active', true);
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
        return $this->image;
    }

    /**
     * Alias pour l'URL de l'image principale
     */
    public function getImageUrlAttribute()
    {
        $mainImage = $this->main_image;
        
        if (!$mainImage) {
            return null;
        }
        
        // Si c'est déjà du base64 ou une URL complète, retourner tel quel
        if (str_starts_with($mainImage, 'data:image/') || str_starts_with($mainImage, 'http')) {
            return $mainImage;
        }
        
        // Si c'est un chemin qui commence par /, l'utiliser tel quel  
        if (str_starts_with($mainImage, '/')) {
            return $mainImage;
        }
        
        // Sinon, construire l'URL avec le préfixe storage
        return "/storage/products/{$mainImage}";
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
        return $this->is_active === true && $this->stock_quantity > 0;
    }

    /**
     * Business Logic
     */
    public function canCreateLottery()
    {
        $ticketPrice = $this->meta['ticket_price'] ?? 100;
        return $this->is_active === true && 
               $this->stock_quantity > 0 && 
               $this->sale_mode === 'lottery' &&
               $ticketPrice >= 100 &&
               !$this->activeLottery()->exists();
    }

    public function getTotalParticipationAmount()
    {
        $ticketPrice = $this->meta['ticket_price'] ?? 100;
        $minParticipants = $this->meta['min_participants'] ?? 1000;
        return $ticketPrice * $minParticipants;
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
     * Calcul automatique du prix des tickets
     */
    public function calculateTicketPrice(int $numberOfTickets = null): float
    {
        $numberOfTickets = $numberOfTickets ?? config('koumbaya.ticket_calculation.default_tickets', 1000);
        
        return TicketPriceCalculator::calculateTicketPrice(
            $this->price,
            $numberOfTickets
        );
    }

    /**
     * Obtenir les détails complets du calcul
     */
    public function getTicketCalculationDetails(int $numberOfTickets = null): array
    {
        $numberOfTickets = $numberOfTickets ?? config('koumbaya.ticket_calculation.default_tickets', 1000);
        
        return TicketPriceCalculator::getCalculationDetails(
            $this->price,
            $numberOfTickets
        );
    }

    /**
     * Obtenir des suggestions de prix pour différents nombres de tickets
     */
    public function getTicketPriceSuggestions(): array
    {
        $ticketPrice = $this->meta['ticket_price'] ?? 0;
        return TicketPriceCalculator::getSuggestions($this->price, $ticketPrice);
    }

    /**
     * Mettre à jour automatiquement le prix du ticket
     */
    public function updateTicketPrice(int $numberOfTickets = null): void
    {
        $numberOfTickets = $numberOfTickets ?? config('koumbaya.ticket_calculation.default_tickets', 1000);
        $meta = $this->meta ?? [];
        $meta['ticket_price'] = $this->calculateTicketPrice($numberOfTickets);
        $meta['min_participants'] = $numberOfTickets;
        $this->meta = $meta;
        $this->save();
    }

    /**
     * Hook pour calculer automatiquement le prix du ticket lors de la sauvegarde
     */
    protected static function booted()
    {
        // Calcul automatique lors de la création
        static::creating(function ($product) {
            if (config('koumbaya.features.auto_calculate_ticket_price', true)) {
                $meta = $product->meta ?? [];
                
                if ($product->price && (!isset($meta['ticket_price']) || $meta['ticket_price'] == 0)) {
                    $meta['ticket_price'] = $product->calculateTicketPrice();
                }
                if (!isset($meta['min_participants'])) {
                    $meta['min_participants'] = config('koumbaya.ticket_calculation.default_tickets', 1000);
                }
                
                $product->meta = $meta;
            }
        });

        // Recalcul automatique lors de la modification du prix
        static::updating(function ($product) {
            if (config('koumbaya.features.auto_calculate_ticket_price', true)) {
                if ($product->isDirty('price') && $product->price) {
                    $meta = $product->meta ?? [];
                    $minParticipants = $meta['min_participants'] ?? 1000;
                    $meta['ticket_price'] = $product->calculateTicketPrice($minParticipants);
                    $product->meta = $meta;
                }
            }
        });
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
            'ticket_price' => (float) ($this->meta['ticket_price'] ?? 0),
            'min_participants' => $this->meta['min_participants'] ?? 1000,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'main_image' => $this->main_image,
            'category_id' => $this->category_id,
            'merchant_id' => $this->merchant_id,
            'is_active' => $this->is_active,
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
