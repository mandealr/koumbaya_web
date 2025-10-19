<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\TicketPriceCalculator;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'images', // Support pour array d'images
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
        'ticket_price', // Compatibilité backward
        'total_tickets', // Nombre de tickets pour tombola
        'min_participants', // Compatibilité backward
        'status', // Compatibilité backward
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
            'images' => 'array', // Cast pour array d'images
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
            ->where('draw_date', '>', now());
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

    public function orders()
    {
        return $this->hasMany(Order::class, 'product_id');
    }

    public function transactions()
    {
        // Les transactions sont maintenant dans la table payments via les orders
        return $this->hasManyThrough(Payment::class, Order::class, 'product_id', 'order_id', 'id', 'id');
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

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->where('sale_mode', '!=', 'direct') // Produits avec tombola toujours visibles
              ->orWhereDoesntHave('orders', function ($orderQuery) {
                  $orderQuery->where('type', 'direct')
                             ->where('status', 'paid');
              });
        });
    }

    /**
     * Accessors
     */
    public function getMainImageAttribute()
    {
        // Priorité : image puis images[0]
        $image = $this->attributes['image'] ?? null;
        $images = $this->images;
        
        // Debug temporaire
        \Log::info('getMainImageAttribute Debug', [
            'product_id' => $this->id,
            'image' => $image,
            'images' => $images,
            'images_type' => gettype($images),
            'images_count' => is_array($images) ? count($images) : 0
        ]);
        
        // Priorité 1: champ image
        if ($image) {
            return $image;
        }
        
        // Priorité 2: premier élément du tableau images
        if ($images && is_array($images) && count($images) > 0) {
            return $images[0];
        }
        
        return null;
    }

    /**
     * Obtenir toutes les images du produit
     */
    public function getImagesAttribute($value)
    {
        // Si on a des images dans l'array, les retourner
        if ($value && is_array(json_decode($value, true))) {
            return json_decode($value, true);
        }
        
        // Si on a des images en JSON string, les décoder
        if ($value && is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        
        // Sinon, retourner l'image principale dans un array si elle existe
        if ($this->attributes['image']) {
            return [$this->attributes['image']];
        }
        
        return [];
    }

    /**
     * Alias pour l'URL de l'image principale
     */
    public function getImageUrlAttribute()
    {
        $mainImage = $this->main_image;
        
        // Debug temporaire
        \Log::info('getImageUrlAttribute Debug', [
            'product_id' => $this->id,
            'main_image' => $mainImage,
            'main_image_type' => gettype($mainImage)
        ]);
        
        if (!$mainImage) {
            return null;
        }
        
        // Si c'est déjà du base64 ou une URL complète, retourner tel quel
        if (str_starts_with($mainImage, 'data:image/') || str_starts_with($mainImage, 'http')) {
            return $mainImage;
        }
        
        // Convertir les anciennes URLs /storage/ vers notre nouvelle route API
        if (str_starts_with($mainImage, '/storage/products/')) {
            $path = str_replace('/storage/', '', $mainImage); // products/2025/09/filename.jpg
            $pathParts = explode('/', $path);
            if (count($pathParts) >= 4) {
                $year = $pathParts[1];
                $month = $pathParts[2];
                $filename = $pathParts[3];
                return config('app.url') . "/api/products/images/{$year}/{$month}/{$filename}";
            }
        }
        
        // Si c'est un chemin qui commence par /, convertir vers route API
        if (str_starts_with($mainImage, '/')) {
            // Essayer d'extraire les parties du chemin
            if (preg_match('#/storage/products/(\d{4})/(\d{2})/(.+)#', $mainImage, $matches)) {
                return config('app.url') . "/api/products/images/{$matches[1]}/{$matches[2]}/{$matches[3]}";
            }
            return $mainImage; // Fallback
        }
        
        // Si ça commence par "products/", construire URL API
        if (str_starts_with($mainImage, 'products/')) {
            $pathParts = explode('/', $mainImage);
            if (count($pathParts) >= 4) {
                $year = $pathParts[1];
                $month = $pathParts[2];
                $filename = $pathParts[3];
                return config('app.url') . "/api/products/images/{$year}/{$month}/{$filename}";
            }
        }
        
        // Sinon, construire l'URL avec notre route API
        return config('app.url') . "/api/products/images/2025/09/{$mainImage}";
    }

    /**
     * Alias pour le nom (compatibilité app mobile)
     */
    public function getTitleAttribute()
    {
        return $this->name;
    }

    /**
     * Accesseur pour ticket_price (compatibilité backward)
     */
    public function getTicketPriceAttribute()
    {
        return $this->meta['ticket_price'] ?? 0;
    }

    /**
     * Accesseur pour total_tickets
     */
    public function getTotalTicketsAttribute()
    {
        return $this->meta['total_tickets'] ?? null;
    }

    /**
     * Mutateur pour ticket_price (compatibilité backward)
     */
    public function setTicketPriceAttribute($value)
    {
        $meta = $this->meta ?? [];
        $meta['ticket_price'] = $value;
        $this->attributes['meta'] = json_encode($meta);
    }

    /**
     * Mutateur pour total_tickets
     */
    public function setTotalTicketsAttribute($value)
    {
        $meta = $this->meta ?? [];
        $meta['total_tickets'] = $value;
        $this->attributes['meta'] = json_encode($meta);
    }

    /**
     * Accesseur pour min_participants (compatibilité backward)
     */
    public function getMinParticipantsAttribute()
    {
        return $this->meta['min_participants'] ?? 1000;
    }

    /**
     * Mutateur pour min_participants (compatibilité backward)
     */
    public function setMinParticipantsAttribute($value)
    {
        $meta = $this->meta ?? [];
        $meta['min_participants'] = $value;
        $this->attributes['meta'] = json_encode($meta);
    }

    /**
     * Accesseur pour status (compatibilité backward)
     */
    public function getStatusAttribute()
    {
        return $this->is_active ? 'active' : 'draft';
    }

    /**
     * Mutateur pour status (compatibilité backward)
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['is_active'] = ($value === 'active');
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
        // Le ticket_price est stocké dans le champ meta JSON
        $meta = is_string($this->meta) ? json_decode($this->meta, true) : ($this->meta ?? []);
        $ticketPrice = $meta['ticket_price'] ?? 100;
        
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
        
        return $lottery->draw_date <= now()->addHours(24) && $lottery->draw_date > now();
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
            'total_revenue' => $this->transactions()->where('status', 'paid')->sum('amount'),
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
                
                // Calculer le prix de ticket seulement pour les produits en mode tombola
                if ($product->price && $product->sale_mode === 'lottery' && (!isset($meta['ticket_price']) || $meta['ticket_price'] == 0)) {
                    $meta['ticket_price'] = $product->calculateTicketPrice();
                }
                
                // Définir min_participants seulement pour les tombolas
                if ($product->sale_mode === 'lottery' && !isset($meta['min_participants'])) {
                    $meta['min_participants'] = config('koumbaya.ticket_calculation.default_tickets', 1000);
                }
                
                $product->meta = $meta;
            }
        });

        // Recalcul automatique lors de la modification du prix
        static::updating(function ($product) {
            if (config('koumbaya.features.auto_calculate_ticket_price', true)) {
                // Recalculer seulement pour les produits en mode tombola
                if ($product->isDirty('price') && $product->price && $product->sale_mode === 'lottery') {
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

    /**
     * Vérifier si un produit peut être supprimé
     *
     * Cette méthode centralise toute la logique de vérification pour la suppression
     * Elle vérifie les commandes, tickets, tombolas et remboursements
     *
     * @return array ['can_delete' => bool, 'errors' => array, 'warnings' => array]
     */
    public function canDeleteProduct(): array
    {
        $errors = [];
        $warnings = [];

        // 1. Vérifier les commandes payées (CRITIQUE - bloque la suppression)
        $paidOrdersCount = $this->orders()
            ->whereIn('status', ['paid', 'fulfilled'])
            ->count();

        if ($paidOrdersCount > 0) {
            $errors[] = [
                'code' => 'PAID_ORDERS_EXIST',
                'message' => "Impossible de supprimer : {$paidOrdersCount} commande(s) payée(s)",
                'count' => $paidOrdersCount,
                'severity' => 'critical'
            ];
        }

        // 2. Vérifier les commandes en attente (AVERTISSEMENT)
        $pendingOrdersCount = $this->orders()
            ->whereIn('status', ['pending', 'awaiting_payment'])
            ->count();

        if ($pendingOrdersCount > 0) {
            $warnings[] = [
                'code' => 'PENDING_ORDERS_EXIST',
                'message' => "Attention : {$pendingOrdersCount} commande(s) en attente de paiement",
                'count' => $pendingOrdersCount,
                'severity' => 'warning'
            ];
        }

        // 3. Pour les produits en mode tombola : vérifications spécifiques
        if ($this->sale_mode === 'lottery') {
            $lotteries = $this->lotteries()->get();

            foreach ($lotteries as $lottery) {
                // Vérifier les tickets payés (source fiable via relation)
                $paidTicketsCount = $lottery->tickets()
                    ->where('status', 'paid')
                    ->count();

                if ($paidTicketsCount > 0) {
                    $errors[] = [
                        'code' => 'PAID_TICKETS_EXIST',
                        'message' => "Tombola #{$lottery->id} : {$paidTicketsCount} ticket(s) vendu(s)",
                        'lottery_id' => $lottery->id,
                        'lottery_number' => $lottery->lottery_number,
                        'count' => $paidTicketsCount,
                        'severity' => 'critical'
                    ];
                }

                // Vérifier si la tombola est active SANS tickets vendus
                // Si active avec tickets vendus, déjà bloqué ci-dessus
                if ($lottery->status === 'active' && $lottery->draw_date > now() && $paidTicketsCount == 0) {
                    $warnings[] = [
                        'code' => 'ACTIVE_LOTTERY_NO_SALES',
                        'message' => "Tombola #{$lottery->id} active sans tickets vendus - sera annulée",
                        'lottery_id' => $lottery->id,
                        'lottery_number' => $lottery->lottery_number,
                        'draw_date' => $lottery->draw_date->toDateTimeString(),
                        'severity' => 'warning'
                    ];
                }

                // Vérifier si la tombola est complétée avec un gagnant
                if ($lottery->status === 'completed' && $lottery->winner_user_id) {
                    $warnings[] = [
                        'code' => 'COMPLETED_LOTTERY_WITH_WINNER',
                        'message' => "Tombola #{$lottery->id} complétée avec gagnant",
                        'lottery_id' => $lottery->id,
                        'winner_id' => $lottery->winner_user_id,
                        'severity' => 'info'
                    ];
                }
            }
        }

        // 4. Vérifier les remboursements en cours (CRITIQUE)
        $pendingRefundsCount = \App\Models\Refund::whereHas('order', function ($query) {
                $query->where('orders.product_id', $this->id);
            })
            ->whereIn('status', ['pending', 'processing', 'approved'])
            ->count();

        if ($pendingRefundsCount > 0) {
            $errors[] = [
                'code' => 'PENDING_REFUNDS_EXIST',
                'message' => "Remboursements en cours : {$pendingRefundsCount}",
                'count' => $pendingRefundsCount,
                'severity' => 'critical'
            ];
        }

        // 5. Vérifier les tickets réservés mais non payés
        if ($this->sale_mode === 'lottery') {
            $reservedTicketsCount = $this->lotteryTickets()
                ->where('lottery_tickets.status', 'reserved')
                ->where('lottery_tickets.created_at', '>', now()->subHours(2)) // Réservations de moins de 2h
                ->count();

            if ($reservedTicketsCount > 0) {
                $warnings[] = [
                    'code' => 'RESERVED_TICKETS_EXIST',
                    'message' => "Tickets réservés (non payés) : {$reservedTicketsCount}",
                    'count' => $reservedTicketsCount,
                    'severity' => 'warning'
                ];
            }
        }

        return [
            'can_delete' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'summary' => [
                'paid_orders' => $paidOrdersCount ?? 0,
                'pending_orders' => $pendingOrdersCount ?? 0,
                'pending_refunds' => $pendingRefundsCount ?? 0,
                'sale_mode' => $this->sale_mode,
                'checked_at' => now()->toISOString()
            ]
        ];
    }

    /**
     * Vérifier rapidement si le produit peut être supprimé (version simple)
     *
     * @return bool
     */
    public function isDeletable(): bool
    {
        return $this->canDeleteProduct()['can_delete'];
    }
}
