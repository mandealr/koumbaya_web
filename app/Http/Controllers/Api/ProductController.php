<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Http\Resources\ProductResource;
use App\Http\Resources\LotteryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\TicketPriceCalculator;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API endpoints pour la gestion des produits"
 * )
 */
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show', 'featured', 'search', 'latest', 'latestDirect', 'latestLottery', 'latestLotteryOnly', 'getBySaleMode']]);
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Lister tous les produits",
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filtrer par catégorie",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="featured",
     *         in="query",
     *         description="Produits mis en avant seulement",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par nom/description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Prix minimum",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Prix maximum",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Trier par",
     *         required=false,
     *         @OA\Schema(type="string", enum={"price_asc", "price_desc", "date_asc", "date_desc", "name_asc", "name_desc", "popularity"})
     *     ),
     *     @OA\Parameter(
     *         name="has_lottery",
     *         in="query",
     *         description="Produits avec tombola active",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="lottery_ending_soon",
     *         in="query",
     *         description="Tombolas se terminant bientôt (24h)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des produits",
     *         @OA\JsonContent(
     *             @OA\Property(property="products", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'merchant', 'activeLottery']);

        // Filtrer par marchand si paramètre "my_products" est présent et user authentifié
        if ($request->boolean('my_products')) {
            $user = auth('sanctum')->user();
            if ($user) {
                $query->where('merchant_id', $user->id);
            }
        }

        // Filtres de base
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sale_mode')) {
            $query->where('sale_mode', $request->sale_mode);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Recherche textuelle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('category', function ($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filtres de prix
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filtre par disponibilité de tombola
        if ($request->boolean('has_lottery')) {
            $query->whereHas('activeLottery');
        }

        // Filtre tombolas se terminant bientôt
        if ($request->boolean('lottery_ending_soon')) {
            $query->whereHas('activeLottery', function ($lotteryQuery) {
                $lotteryQuery->where('draw_date', '<=', now()->addHours(24))
                            ->where('draw_date', '>', now());
            });
        }

        // Exclure les produits en achat direct qui ont déjà été vendus
        // (sauf si on affiche "my_products" - pour que les marchands voient leurs produits vendus)
        if (!$request->boolean('my_products')) {
            $query->available();
        }

        // Tri des résultats
        $this->applySorting($query, $request->get('sort_by', 'date_desc'));

        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $products = $query->paginate($perPage);

        // Ajouter des métadonnées utiles
        $products->getCollection()->transform(function ($product) {
            $product->append(['has_active_lottery', 'lottery_ends_soon', 'popularity_score']);
            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => ProductResource::collection($products->items()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ],
            'filters' => [
                'available_categories' => \App\Models\Category::whereHas('products')->get(['id', 'name']),
                'price_range' => [
                    'min' => Product::min('price'),
                    'max' => Product::max('price')
                ]
            ]
        ]);
    }

    /**
     * Synchroniser les données de tombola avec la réalité
     */
    private function syncLotteryData($product)
    {
        if ($product && $product->activeLottery) {
            try {
                // Rafraîchir sold_tickets avec le nombre réel de tickets payés
                $realSoldTickets = $product->activeLottery->paidTickets()->count();
                if ($realSoldTickets != $product->activeLottery->sold_tickets) {
                    $product->activeLottery->update(['sold_tickets' => $realSoldTickets]);
                    $product->activeLottery->refresh();
                }
                
                $product->activeLottery->append(['remaining_tickets', 'progress_percentage', 'time_remaining', 'participation_rate', 'is_ending_soon']);
            } catch (\Exception $e) {
                // Log l'erreur mais continue sans crash
                \Log::warning('Erreur lors de la synchronisation des données de tombola: ' . $e->getMessage());
            }
        }
        return $product;
    }

    /**
     * Appliquer le tri aux résultats
     */
    private function applySorting($query, $sortBy)
    {
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'date_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'date_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'popularity':
                $query->withCount('lotteryTickets')
                      ->orderBy('lottery_tickets_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/search",
     *     tags={"Products"},
     *     summary="Recherche intelligente avec auto-complétion",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Terme de recherche",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de suggestions (max 10)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Suggestions de recherche",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="suggestions", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="type", type="string", example="product"),
     *                         @OA\Property(property="title", type="string", example="iPhone 15 Pro"),
     *                         @OA\Property(property="subtitle", type="string", example="Électronique"),
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="price", type="number", example=750000)
     *                     )
     *                 ),
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:10'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $searchTerm = $request->q;
        $limit = $request->get('limit', 5);

        $suggestions = [];
        $categories = [];

        // Recherche de produits
        $products = Product::with(['category', 'activeLottery'])
            ->available() // Exclure les produits en achat direct déjà vendus
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            })
            ->limit($limit)
            ->get();

        foreach ($products as $product) {
            $suggestions[] = [
                'type' => 'product',
                'id' => $product->id,
                'title' => $product->name,
                'subtitle' => $product->category->name ?? 'Produit',
                'price' => $product->price,
                'image' => $product->image_url,
                'has_lottery' => $product->activeLottery !== null,
                'match_score' => $this->calculateMatchScore($searchTerm, $product->name)
            ];
        }

        // Recherche de catégories
        $categoryMatches = \App\Models\Category::where('name', 'LIKE', "%{$searchTerm}%")
            ->whereHas('products')
            ->limit(3)
            ->get(['id', 'name', 'slug']);

        foreach ($categoryMatches as $category) {
            $categories[] = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'type' => 'category'
            ];
        }

        // Trier les suggestions par score de pertinence
        usort($suggestions, function ($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        return $this->sendResponse([
            'suggestions' => $suggestions,
            'categories' => $categories,
            'search_term' => $searchTerm,
            'total_found' => count($suggestions)
        ]);
    }

    /**
     * Calculer le score de correspondance
     */
    private function calculateMatchScore($searchTerm, $title)
    {
        $searchTerm = strtolower($searchTerm);
        $title = strtolower($title);
        
        // Score exact match
        if ($title === $searchTerm) return 100;
        
        // Score starts with
        if (str_starts_with($title, $searchTerm)) return 80;
        
        // Score contains
        if (str_contains($title, $searchTerm)) return 60;
        
        // Score similarity
        return (int) (similar_text($searchTerm, $title) / strlen($title) * 40);
    }

    /**
     * @OA\Get(
     *     path="/api/products/featured",
     *     tags={"Products"},
     *     summary="Produits mis en avant",
     *     @OA\Response(response=200, description="Produits mis en avant")
     * )
     */
    public function featured()
    {
        $products = Product::active()
            ->featured()
            ->available() // Exclure les produits en achat direct déjà vendus
            ->with(['category', 'merchant', 'activeLottery'])
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Détails d'un produit",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du produit",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du produit",
     *         @OA\JsonContent(
     *             @OA\Property(property="product", type="object")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $product = Product::with([
            'category', 
            'merchant', 
            'activeLottery.tickets', 
            'lotteries' => function($query) {
                $query->completed()->with('winner')->orderBy('draw_date', 'desc')->limit(5);
            }
        ])->findOrFail($id);

        return $this->sendResponse(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Créer un nouveau produit (Marchands seulement)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","price","ticket_price","category_id"},
     *             @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *             @OA\Property(property="description", type="string", example="Smartphone Apple dernière génération"),
     *             @OA\Property(property="price", type="number", format="float", example=850000),
     *             @OA\Property(property="ticket_price", type="number", format="float", example=2500),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="min_participants", type="integer", example=150)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Produit créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="product", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Vérifier que l'utilisateur est un marchand
        if (!$user->is_merchant) {
            return response()->json(['error' => 'Seuls les marchands peuvent créer des produits'], 403);
        }

        // Build validation rules based on sale mode
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:1000',
            'category_id' => 'required|exists:categories,id',
            'sale_mode' => 'required|string|in:direct,lottery',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'vendor_profile_id' => 'nullable|exists:vendor_profiles,id',
        ];

        // Add lottery-specific validation rules only if lottery mode
        if ($request->sale_mode === 'lottery') {
            $rules['total_tickets'] = 'required|integer|min:10|max:10000'; // Nombre de tickets requis
            $rules['ticket_price'] = 'nullable|numeric|min:100'; // Calculé automatiquement
            $rules['min_participants'] = 'nullable|integer|min:10|max:10000';
            $rules['lottery_duration'] = 'nullable|integer|min:1|max:60'; // Durée en jours
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation spécifique pour la durée de tombola selon le type de vendeur
        if ($request->sale_mode === 'lottery' && $request->filled('lottery_duration')) {
            $durationValidation = $this->validateLotteryDurationForUser($user, $request->lottery_duration);
            if (!$durationValidation['valid']) {
                return response()->json([
                    'error' => $durationValidation['message'],
                    'allowed_range' => $durationValidation['allowed_range'] ?? null
                ], 422);
            }
        }

        // Vérifier le profil vendeur si fourni
        $vendor = null;
        if ($request->vendor_profile_id) {
            $vendor = \App\Models\VendorProfile::where('id', $request->vendor_profile_id)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$vendor) {
                return response()->json(['error' => 'Profil vendeur non trouvé ou non autorisé'], 403);
            }
        }

        // Pour mode tombola, calculer automatiquement le prix du ticket basé sur le nombre de tickets fourni
        $ticketPrice = $request->ticket_price;
        $totalTickets = $request->total_tickets;
        
        if ($request->sale_mode === 'lottery') {
            // Utiliser la nouvelle logique: calculer le prix basé sur le nombre de tickets fourni
            $ticketPrice = \App\Services\TicketPriceCalculator::calculateTicketPrice(
                $request->price,
                $totalTickets ?? 1000, // Utiliser le nombre fourni par l'utilisateur
                0.10, // Commission par défaut (10%)
                0.15, // Marge par défaut (15%)
                $user // Utiliser l'utilisateur pour les contraintes
            );
            
            // Valider le prix calculé
            $validation = \App\Services\TicketPriceCalculator::validateTicketPrice($ticketPrice);
            if (!$validation['is_valid']) {
                return response()->json([
                    'error' => 'Prix de ticket invalide selon les contraintes',
                    'warnings' => $validation['warnings']
                ], 422);
            }
        }

        // Prepare product data
        $images = $request->images ?? [];
        
        // Debug temporaire
        \Log::info('ProductController: Creating product with images', [
            'images_received' => $images,
            'images_type' => gettype($images),
            'images_count' => is_array($images) ? count($images) : 0,
            'first_image' => is_array($images) && count($images) > 0 ? $images[0] : null
        ]);
        
        $productData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . time(),
            'description' => $request->description,
            'images' => $images,
            'image' => is_array($images) && count($images) > 0 ? $images[0] : null, // Image principale
            'price' => $request->price,
            'category_id' => $request->category_id,
            'merchant_id' => $user->id,
            'sale_mode' => $request->sale_mode,
            'stock_quantity' => 1,
            'status' => 'active',
            'vendor_profile_id' => $vendor ? $vendor->id : null,
        ];

        // Add lottery-specific fields only if lottery mode
        if ($request->sale_mode === 'lottery') {
            // Store lottery data in meta field (existing pattern in codebase)
            $productData['meta'] = [
                'ticket_price' => $ticketPrice,
                'total_tickets' => $totalTickets,
                'min_participants' => $request->min_participants ?? $totalTickets,
            ];
        }

        $product = Product::create($productData);

        // Si c'est un produit tombola, créer automatiquement la tombola
        if ($request->sale_mode === 'lottery') {
            // Utiliser la nouvelle logique: l'utilisateur a fourni le nombre de tickets
            // et nous avons calculé le prix du ticket

            // Déterminer la durée selon le type de vendeur
            $lotteryDuration = $this->getLotteryDurationForUser($user, $request->lottery_duration);

            // Vérifier si l'utilisateur a un abonnement premium
            $isPremium = $this->checkPremiumStatus($user);

            // Limiter le nombre de tickets à 500 pour les utilisateurs non premium
            $maxAllowedTickets = $isPremium ? $totalTickets : min($totalTickets, 500);

            if (!$isPremium && $totalTickets > 500) {
                \Log::warning('Nombre de tickets limité à 500 (non premium)', [
                    'user_id' => $user->id,
                    'requested_tickets' => $totalTickets,
                    'allowed_tickets' => $maxAllowedTickets
                ]);
            }

            // Recalculer le prix du ticket si le nombre a été limité
            if ($maxAllowedTickets != $totalTickets) {
                $ticketPrice = round($product->price / $maxAllowedTickets, 0);
            }

            $product->lotteries()->create([
                'lottery_number' => 'LOT-' . strtoupper(Str::random(8)),
                'title' => 'Tombola - ' . $product->name,
                'description' => 'Tombola pour le produit : ' . $product->name,
                'ticket_price' => $ticketPrice,
                'max_tickets' => $maxAllowedTickets,
                'sold_tickets' => 0,
                'currency' => 'XAF',
                'draw_date' => now()->addDays($lotteryDuration),
                'status' => 'active',
                'meta' => [
                    'auto_created' => true,
                    'created_with_product' => true,
                    'vendor_profile_id' => $vendor ? $vendor->id : null,
                    'duration_days' => $lotteryDuration,
                    'calculation_method' => 'tickets_to_price', // Nouvelle méthode de calcul
                    'is_premium' => $isPremium,
                    'original_max_tickets' => $totalTickets,
                    'limited_to_500' => !$isPremium && $totalTickets > 500
                ]
            ]);
        }

        $product->load(['category', 'merchant', 'activeLottery']);

        return response()->json([
            'message' => 'Produit créé avec succès',
            'product' => $product,
            'ticket_price_calculated' => $request->sale_mode === 'lottery' && !$request->ticket_price,
        ], 201);
    }

    /**
     * Get products for the authenticated merchant
     */
    public function merchantProducts(Request $request)
    {
        $user = auth()->user();
        $perPage = min($request->get('per_page', 15), 50);

        $query = Product::where('merchant_id', $user->id)
            ->with([
                'category', 
                'lotteries' => function($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'orders' => function($q) {
                    $q->whereIn('status', ['paid', 'fulfilled']);
                }
            ])
            ->withCount([
                'orders as paid_orders_count' => function($q) {
                    $q->whereIn('status', ['paid', 'fulfilled']);
                }
            ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sale_mode')) {
            $query->where('sale_mode', $request->sale_mode);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at_desc');
        $this->applySorting($query, $sortBy);

        $products = $query->paginate($perPage);

        // Transform products to include lottery progression data and revenue info
        $transformedProducts = $products->getCollection()->map(function ($product) {
            $productData = $product->toArray();
            
            // Add lottery progression data
            if ($product->sale_mode === 'lottery' && $product->lotteries->count() > 0) {
                $lottery = $product->lotteries->first(); // Most recent lottery
                $productData['lottery'] = [
                    'id' => $lottery->id,
                    'sold_tickets' => $lottery->sold_tickets ?? 0,
                    'max_tickets' => $lottery->max_tickets ?? 0,
                    'ticket_price' => $lottery->ticket_price ?? 0,
                    'status' => $lottery->status
                ];
                $productData['lottery_progression'] = [
                    'sold_tickets' => $lottery->sold_tickets ?? 0,
                    'max_tickets' => $lottery->max_tickets ?? 0,
                    'progress_percentage' => $lottery->max_tickets > 0 ? 
                        round(($lottery->sold_tickets / $lottery->max_tickets) * 100, 1) : 0,
                ];
                
                // Calculate revenue for lottery
                $productData['revenue'] = ($lottery->sold_tickets ?? 0) * ($lottery->ticket_price ?? 0);
            } else {
                $productData['lottery'] = null;
                $productData['lottery_progression'] = null;
                
                // Calculate revenue for direct sales from orders
                $productData['revenue'] = $product->orders->sum('total_amount');
            }
            
            // Add sales information for deletion logic
            $productData['paid_orders_count'] = $product->paid_orders_count ?? 0;
            $productData['can_delete'] = $product->paid_orders_count == 0 && 
                                       ($product->sale_mode !== 'lottery' || 
                                        !$product->lotteries->first() || 
                                        $product->lotteries->first()->sold_tickets == 0);
            
            return $productData;
        });

        return response()->json([
            'data' => $transformedProducts,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem()
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Mettre à jour un produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du produit",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Produit mis à jour"),
     *     @OA\Response(response=403, description="Non autorisé")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $product = Product::findOrFail($id);

        // Vérifier que l'utilisateur est le propriétaire
        if ($product->merchant_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Ne pas permettre la modification si une tombola est active
        if ($product->activeLottery) {
            return response()->json(['error' => 'Impossible de modifier un produit avec une tombola active'], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:1000',
            'ticket_price' => 'numeric|min:100',
            'images' => 'nullable|array',
            'status' => 'in:draft,active',
            'vendor_profile_id' => 'nullable|exists:vendor_profiles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier le profil vendeur si fourni
        $vendor = null;
        if ($request->has('vendor_profile_id') && $request->vendor_profile_id) {
            $vendor = \App\Models\VendorProfile::where('id', $request->vendor_profile_id)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$vendor) {
                return response()->json(['error' => 'Profil vendeur non trouvé ou non autorisé'], 403);
            }
            
            // Si c'est un produit tombola et qu'un prix de ticket est fourni, valider avec le profil
            if ($product->sale_mode === 'lottery' && $request->has('ticket_price')) {
                $validation = \App\Services\TicketPriceCalculator::validateTicketPrice($request->ticket_price, $vendor);
                if (!$validation['is_valid']) {
                    return response()->json([
                        'error' => 'Prix de ticket invalide selon les contraintes du profil vendeur',
                        'warnings' => $validation['warnings']
                    ], 422);
                }
            }
        }

        $updateData = $request->only([
            'name', 'description', 'price', 'ticket_price', 'images', 'status'
        ]);
        
        // Ajouter vendor_profile_id seulement si explicitement fourni (peut être null pour retirer)
        if ($request->has('vendor_profile_id')) {
            $updateData['vendor_profile_id'] = $request->vendor_profile_id;
        }

        $product->update($updateData);

        return response()->json([
            'message' => 'Produit mis à jour avec succès',
            'product' => $product->load(['category', 'merchant', 'vendorProfile'])
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/products/{id}/create-lottery",
     *     tags={"Products"},
     *     summary="Créer une tombola pour un produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du produit",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="duration_days", type="integer", example=7, description="Durée en jours")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Tombola créée")
     * )
     */
    public function createLottery(Request $request, $id)
    {
        $user = auth()->user();
        $product = Product::findOrFail($id);

        if ($product->merchant_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Charger le profil vendeur s'il existe
        $vendor = null;
        if ($product->vendor_profile_id) {
            $vendor = $product->vendorProfile;
        }

        if (!$product->canCreateLottery()) {
            $reasons = [];
            
            if (!$product->is_active) {
                $reasons[] = 'Le produit doit être actif';
            }
            
            if ($product->stock_quantity <= 0) {
                $reasons[] = 'Le produit doit avoir du stock disponible';
            }
            
            if ($product->sale_mode !== 'lottery') {
                $reasons[] = 'Le mode de vente doit être défini sur "Tombola"';
            }
            
            $ticketPrice = $product->meta['ticket_price'] ?? 100;
            if ($ticketPrice < 100) {
                $reasons[] = 'Le prix du ticket doit être d\'au moins 100 FCFA';
            }
            
            if ($product->activeLottery()->exists()) {
                $reasons[] = 'Une tombola est déjà active pour ce produit';
            }
            
            // Validation supplémentaire avec le profil vendeur
            if ($vendor) {
                $validation = \App\Services\TicketPriceCalculator::validateTicketPrice($ticketPrice, $vendor);
                if (!$validation['is_valid']) {
                    $reasons = array_merge($reasons, $validation['warnings']);
                }
            }
            
            return response()->json([
                'error' => 'Impossible de créer une tombola pour ce produit',
                'details' => $reasons,
                'debug' => [
                    'is_active' => $product->is_active,
                    'stock_quantity' => $product->stock_quantity,
                    'sale_mode' => $product->sale_mode,
                    'ticket_price' => $ticketPrice,
                    'has_active_lottery' => $product->activeLottery()->exists(),
                    'vendor_profile' => $vendor ? [
                        'type' => $vendor->type,
                        'constraints' => $vendor->getConstraintsAttribute()
                    ] : null
                ]
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'duration_days' => 'required|integer|min:1|max:30',
            'ticket_price' => 'nullable|numeric|min:100', // Permettre de modifier le prix du ticket
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Si un nouveau prix de ticket est fourni, valider avec le profil vendeur
        $ticketPrice = $request->ticket_price ?? $product->ticket_price;
        if ($vendor && $request->has('ticket_price')) {
            $validation = \App\Services\TicketPriceCalculator::validateTicketPrice($ticketPrice, $vendor);
            if (!$validation['is_valid']) {
                return response()->json([
                    'error' => 'Prix de ticket invalide selon les contraintes du profil vendeur',
                    'warnings' => $validation['warnings']
                ], 422);
            }
        }

        $totalTickets = (int) ceil($product->price / $ticketPrice);
        
        $lottery = Lottery::create([
            'lottery_number' => 'KMB-' . date('Y') . '-' . str_pad($product->id, 6, '0', STR_PAD_LEFT),
            'product_id' => $product->id,
            'total_tickets' => $totalTickets,
            'ticket_price' => $ticketPrice,
            'start_date' => now(),
            'end_date' => now()->addDays($request->duration_days),
            'status' => 'active',
            'meta' => [
                'vendor_profile_id' => $vendor ? $vendor->id : null,
            ]
        ]);

        // Mettre à jour le statut du produit et le prix du ticket si modifié
        $updateData = ['status' => 'active'];
        if ($request->has('ticket_price')) {
            $updateData['ticket_price'] = $ticketPrice;
        }
        $product->update($updateData);

        return response()->json([
            'message' => 'Tombola créée avec succès',
            'lottery' => $lottery->load('product'),
            'vendor_profile_used' => $vendor !== null
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/products/latest",
     *     tags={"Products"},
     *     summary="Récupérer les derniers produits",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de produits à retourner",
     *         required=false,
     *         @OA\Schema(type="integer", default=8)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des derniers produits",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function latest(Request $request)
    {
        $limit = min($request->get('limit', 8), 20);
        
        $products = Product::with(['category', 'merchant', 'activeLottery'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Ajouter des métadonnées utiles
        $products->each(function ($product) {
            $product->append(['has_active_lottery', 'lottery_ends_soon', 'popularity_score', 'image_url']);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => ProductResource::collection($products),
                'count' => $products->count()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/latest-lottery",
     *     tags={"Products"},
     *     summary="Récupérer le dernier produit tombola actif",
     *     @OA\Response(
     *         response=200,
     *         description="Dernier produit tombola",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun produit tombola trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function latestLottery(Request $request)
    {
        try {
            // Version simple sans synchronisation pour débugger
            $product = Product::with(['category', 'merchant', 'activeLottery'])
                ->where('is_active', true)
                ->where('sale_mode', 'lottery')
                ->whereHas('activeLottery', function ($query) {
                    $query->where('status', 'active')
                          ->where('draw_date', '>', now());
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucun produit tombola actif trouvé'
                ], 200);
            }

            // Ajouter des métadonnées utiles
            $product->append(['has_active_lottery', 'lottery_ends_soon', 'popularity_score', 'image_url']);

            // Synchroniser les données de tombola SEULEMENT si activeLottery existe
            if ($product->activeLottery) {
                // Vérifier et corriger sold_tickets si nécessaire
                $realSoldTickets = $product->activeLottery->paidTickets()->count();
                if ($realSoldTickets != $product->activeLottery->sold_tickets) {
                    \Log::info("Synchronisation tickets pour lottery {$product->activeLottery->id}: {$product->activeLottery->sold_tickets} -> {$realSoldTickets}");
                    $product->activeLottery->update(['sold_tickets' => $realSoldTickets]);
                    $product->activeLottery->refresh();
                }
                
                $product->activeLottery->append(['remaining_tickets', 'progress_percentage', 'time_remaining', 'participation_rate', 'is_ending_soon']);
            }

            // Debug des données before return
            $lotteryDebug = null;
            if ($product->activeLottery) {
                $lotteryDebug = [
                    'id' => $product->activeLottery->id,
                    'sold_tickets' => $product->activeLottery->sold_tickets,
                    'max_tickets' => $product->activeLottery->max_tickets,
                    'progress_percentage' => $product->activeLottery->progress_percentage,
                    'real_paid_tickets_count' => $product->activeLottery->paidTickets()->count(),
                ];
                \Log::info('Latest lottery debug:', $lotteryDebug);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => new ProductResource($product),
                    'lottery' => $product->activeLottery ? new LotteryResource($product->activeLottery) : null
                ],
                'debug' => $lotteryDebug // Temporaire pour debug
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans latestLottery: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du produit tombola',
                'error' => app()->environment('local') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/latest-direct",
     *     tags={"Products"},
     *     summary="Récupérer les derniers produits en achat direct",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de produits à retourner",
     *         required=false,
     *         @OA\Schema(type="integer", default=8)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des derniers produits en achat direct",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function latestDirect(Request $request)
    {
        $limit = min($request->get('limit', 8), 20);
        
        $products = Product::with(['category', 'merchant'])
            ->where('is_active', true)
            ->where('sale_mode', 'direct')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Ajouter des métadonnées utiles
        $products->each(function ($product) {
            $product->append(['image_url']);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => ProductResource::collection($products),
                'count' => $products->count(),
                'sale_mode' => 'direct'
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/latest-lottery-only",
     *     tags={"Products"},
     *     summary="Récupérer les derniers produits tombola uniquement",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de produits à retourner",
     *         required=false,
     *         @OA\Schema(type="integer", default=8)
     *     ),
     *     @OA\Parameter(
     *         name="active_only",
     *         in="query",
     *         description="Produits avec tombola active uniquement",
     *         required=false,
     *         @OA\Schema(type="boolean", default=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des derniers produits tombola",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function latestLotteryOnly(Request $request)
    {
        $limit = min($request->get('limit', 8), 20);
        $activeOnly = $request->boolean('active_only', true);
        
        $query = Product::with(['category', 'merchant', 'activeLottery'])
            ->where('is_active', true)
            ->where('sale_mode', 'lottery');

        // Si on veut seulement les tombolas actives
        if ($activeOnly) {
            $query->whereHas('activeLottery', function ($lotteryQuery) {
                $lotteryQuery->where('status', 'active')
                              ->where('draw_date', '>', now());
            });
        }

        $products = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Ajouter des métadonnées utiles
        $products->each(function ($product) {
            $product->append(['has_active_lottery', 'lottery_ends_soon', 'popularity_score', 'image_url']);
            
            // Synchroniser les données de tombola
            $this->syncLotteryData($product);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => ProductResource::collection($products),
                'count' => $products->count(),
                'sale_mode' => 'lottery',
                'active_only' => $activeOnly
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/by-sale-mode/{mode}",
     *     tags={"Products"},
     *     summary="Récupérer les produits par mode de vente",
     *     @OA\Parameter(
     *         name="mode",
     *         in="path",
     *         description="Mode de vente (direct ou lottery)",
     *         required=true,
     *         @OA\Schema(type="string", enum={"direct", "lottery"})
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de produits à retourner",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page à récupérer",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des produits par mode de vente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getBySaleMode(Request $request, $mode)
    {
        if (!in_array($mode, ['direct', 'lottery'])) {
            return response()->json([
                'success' => false,
                'message' => 'Mode de vente invalide. Utilisez "direct" ou "lottery".'
            ], 400);
        }

        $query = Product::with(['category', 'merchant']);
        
        // Ajouter la relation activeLottery pour les produits tombola
        if ($mode === 'lottery') {
            $query->with('activeLottery');
        }

        $query->where('is_active', true)
              ->where('sale_mode', $mode);

        // Pour les tombolas, on peut filtrer sur les actives seulement
        if ($mode === 'lottery' && $request->boolean('active_lottery_only')) {
            $query->whereHas('activeLottery', function ($lotteryQuery) {
                $lotteryQuery->where('status', 'active')
                              ->where('draw_date', '>', now());
            });
        }

        // Tri des résultats
        $this->applySorting($query, $request->get('sort_by', 'date_desc'));

        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $products = $query->paginate($perPage);

        // Ajouter des métadonnées selon le mode
        $products->getCollection()->transform(function ($product) use ($mode) {
            if ($mode === 'lottery') {
                $product->append(['has_active_lottery', 'lottery_ends_soon', 'popularity_score', 'image_url']);
                $this->syncLotteryData($product);
            } else {
                $product->append(['image_url']);
            }
            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => ProductResource::collection($products->items()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
                'sale_mode' => $mode
            ]
        ]);
    }

    /**
     * Détermine la durée de tombola appropriée selon le type de vendeur
     * 
     * @param \App\Models\User $user
     * @param int|null $requestedDuration
     * @return int durée en jours
     */
    private function getLotteryDurationForUser($user, $requestedDuration = null)
    {
        $sellerProfiles = config('koumbaya.features.seller_profiles');
        
        // Vendeur particulier/individuel - durée fixe
        if ($user->isIndividualSeller()) {
            return $sellerProfiles['individual']['lottery_duration']['fixed'];
        }
        
        // Vendeur business/entreprise - peut configurer
        if ($user->isBusinessSeller()) {
            $businessConfig = $sellerProfiles['business']['lottery_duration'];
            
            // Si aucune durée demandée, utiliser la durée par défaut
            if (!$requestedDuration) {
                return config('koumbaya.marketplace.default_lottery_duration', 30);
            }
            
            // Valider la durée demandée dans les limites business
            $minDays = $businessConfig['min_days'];
            $maxDays = $businessConfig['max_days'];
            
            if ($requestedDuration < $minDays || $requestedDuration > $maxDays) {
                // Si hors limites, utiliser la durée par défaut
                return config('koumbaya.marketplace.default_lottery_duration', 30);
            }
            
            return $requestedDuration;
        }
        
        // Par défaut (marchands standard ou autres)
        return config('koumbaya.marketplace.default_lottery_duration', 30);
    }

    /**
     * Vérifie si l'utilisateur a un statut premium
     *
     * @param \App\Models\User $user
     * @return bool
     */
    private function checkPremiumStatus($user)
    {
        // Vérifier si l'utilisateur a un role premium ou un abonnement actif
        // Pour le moment, on considère les Business Enterprise comme premium
        return $user->hasRole('Business Enterprise') ||
               $user->hasRole('Admin') ||
               ($user->meta && isset($user->meta['is_premium']) && $user->meta['is_premium']);
    }

    /**
     * Valide la durée de tombola selon le type de vendeur
     *
     * @param \App\Models\User $user
     * @param int $duration durée demandée en jours
     * @return array résultat de la validation
     */
    private function validateLotteryDurationForUser($user, $duration)
    {
        $sellerProfiles = config('koumbaya.features.seller_profiles');
        
        // Vendeur particulier/individuel - durée fixe uniquement
        if ($user->isIndividualSeller()) {
            $fixedDuration = $sellerProfiles['individual']['lottery_duration']['fixed'];
            
            if ($duration != $fixedDuration) {
                return [
                    'valid' => false,
                    'message' => "Les vendeurs particuliers peuvent uniquement créer des tombolas de {$fixedDuration} jours",
                    'allowed_range' => ['fixed' => $fixedDuration]
                ];
            }
            
            return ['valid' => true];
        }
        
        // Vendeur business/entreprise - peut configurer dans les limites
        if ($user->isBusinessSeller()) {
            $businessConfig = $sellerProfiles['business']['lottery_duration'];
            $minDays = $businessConfig['min_days'];
            $maxDays = $businessConfig['max_days'];
            
            if ($duration < $minDays || $duration > $maxDays) {
                return [
                    'valid' => false,
                    'message' => "Les vendeurs business peuvent créer des tombolas entre {$minDays} et {$maxDays} jours",
                    'allowed_range' => ['min' => $minDays, 'max' => $maxDays]
                ];
            }
            
            return ['valid' => true];
        }
        
        // Vendeur standard - utiliser les limites par défaut
        if ($duration < 1 || $duration > 60) {
            return [
                'valid' => false,
                'message' => "La durée doit être entre 1 et 60 jours",
                'allowed_range' => ['min' => 1, 'max' => 60]
            ];
        }
        
        return ['valid' => true];
    }

    /**
     * @OA\Get(
     *     path="/api/products/lottery-duration-constraints",
     *     tags={"Products"},
     *     summary="Obtenir les contraintes de durée de tombola pour le vendeur connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Contraintes de durée de tombola",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getLotteryDurationConstraints()
    {
        $user = auth()->user();
        
        // Si pas d'utilisateur authentifié, retourner les contraintes génériques
        if (!$user) {
            return $this->getGenericConstraints();
        }
        
        if (!$user->isMerchant()) {
            return response()->json(['error' => 'Seuls les marchands peuvent accéder à ces informations'], 403);
        }
        
        $sellerProfiles = config('koumbaya.features.seller_profiles');
        $constraints = [];
        
        // Vendeur particulier/individuel
        if ($user->isIndividualSeller()) {
            $constraints = [
                'type' => 'individual',
                'can_customize' => false,
                'fixed_duration' => $sellerProfiles['individual']['lottery_duration']['fixed'],
                'min_days' => $sellerProfiles['individual']['lottery_duration']['fixed'],
                'max_days' => $sellerProfiles['individual']['lottery_duration']['fixed'],
                'description' => 'Durée fixe de 30 jours pour les vendeurs particuliers'
            ];
        }
        // Vendeur business/entreprise
        elseif ($user->isBusinessSeller()) {
            $constraints = [
                'type' => 'business',
                'can_customize' => true,
                'fixed_duration' => null,
                'min_days' => $sellerProfiles['business']['lottery_duration']['min_days'],
                'max_days' => $sellerProfiles['business']['lottery_duration']['max_days'],
                'description' => 'Configurez la durée entre 1 et 60 jours pour les vendeurs business'
            ];
        }
        // Vendeur standard
        else {
            $constraints = [
                'type' => 'standard',
                'can_customize' => true,
                'fixed_duration' => null,
                'min_days' => 1,
                'max_days' => 60,
                'description' => 'Configurez la durée entre 1 et 60 jours'
            ];
        }
        
        $constraints['default_duration'] = config('koumbaya.marketplace.default_lottery_duration', 30);
        
        // Ajouter les informations sur les prix de tickets
        $constraints['ticket_pricing'] = [
            'min_ticket_price' => config('koumbaya.ticket_calculation.min_ticket_price', 200),
            'max_ticket_price' => config('koumbaya.ticket_calculation.max_ticket_price', 50000),
            'currency' => 'FCFA',
            'rules' => [
                'Le prix de ticket minimum est de ' . config('koumbaya.ticket_calculation.min_ticket_price', 200) . ' FCFA',
                'Le prix est calculé automatiquement selon le prix du produit et la durée',
                'Plus le prix du produit est élevé, plus le prix du ticket sera élevé'
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'constraints' => $constraints,
                'user_type' => [
                    'is_individual_seller' => $user->isIndividualSeller(),
                    'is_business_seller' => $user->isBusinessSeller(),
                    'is_merchant' => $user->is_merchant
                ]
            ]
        ]);
    }

    /**
     * Retourne les contraintes génériques pour les utilisateurs non authentifiés
     */
    private function getGenericConstraints()
    {
        $constraints = [
            'type' => 'standard',
            'can_customize' => true,
            'fixed_duration' => null,
            'min_days' => 1,
            'max_days' => 60,
            'description' => 'Configurez la durée entre 1 et 60 jours',
            'default_duration' => config('koumbaya.marketplace.default_lottery_duration', 30),
            'ticket_pricing' => [
                'min_ticket_price' => config('koumbaya.ticket_calculation.min_ticket_price', 200),
                'max_ticket_price' => config('koumbaya.ticket_calculation.max_ticket_price', 50000),
                'currency' => 'FCFA',
                'rules' => [
                    'Le prix de ticket minimum est de ' . config('koumbaya.ticket_calculation.min_ticket_price', 200) . ' FCFA',
                    'Le prix est calculé automatiquement selon le prix du produit et la durée',
                    'Plus le prix du produit est élevé, plus le prix du ticket sera élevé'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'constraints' => $constraints,
                'timestamp' => now()->toISOString(),
                'version' => '1.0'
            ]
        ]);
    }
}
