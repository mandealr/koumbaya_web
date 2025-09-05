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
            $rules['ticket_price'] = 'nullable|numeric|min:100'; // Optionnel, sera calculé si non fourni
            $rules['min_participants'] = 'nullable|integer|min:10|max:10000';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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

        // Si mode tombola et pas de prix de ticket fourni, calculer automatiquement
        $ticketPrice = $request->ticket_price;
        if ($request->sale_mode === 'lottery' && !$ticketPrice) {
            // Utiliser le service de calcul avec le profil vendeur
            $ticketPrice = \App\Services\TicketPriceCalculator::calculateTicketPrice(
                $request->price,
                1000, // Nombre de tickets par défaut
                null, // Commission par défaut
                null, // Marge par défaut
                $vendor
            );
            
            // Valider le prix calculé avec le profil vendeur
            $validation = \App\Services\TicketPriceCalculator::validateTicketPrice($ticketPrice, $vendor);
            if (!$validation['is_valid']) {
                return response()->json([
                    'error' => 'Prix de ticket invalide selon les contraintes du profil vendeur',
                    'warnings' => $validation['warnings']
                ], 422);
            }
        }

        // Prepare product data
        $productData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . time(),
            'description' => $request->description,
            'images' => $request->images,
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
            $productData['ticket_price'] = $ticketPrice;
            $productData['min_participants'] = $request->min_participants ?? 50;
        }

        $product = Product::create($productData);

        // Si c'est un produit tombola, créer automatiquement la tombola
        if ($request->sale_mode === 'lottery') {
            $totalTickets = (int) ceil($product->price / $product->ticket_price);
            
            $product->lotteries()->create([
                'lottery_number' => 'LOT-' . strtoupper(Str::random(8)),
                'ticket_price' => $product->ticket_price,
                'total_tickets' => $totalTickets,
                'min_participants' => $product->min_participants ?? 50,
                'start_date' => now(),
                'end_date' => now()->addDays(30), // 30 jours par défaut
                'status' => 'active',
                'meta' => [
                    'auto_created' => true,
                    'created_with_product' => true,
                    'vendor_profile_id' => $vendor ? $vendor->id : null,
                ]
            ]);
        }

        $product->load(['category', 'merchant', 'activeLottery', 'vendorProfile']);

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
            ->with(['category', 'activeLottery']);

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

        return response()->json([
            'data' => $products->items(),
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

        // Charger les détails de la tombola active
        $activeLottery = $product->activeLottery;
        if ($activeLottery) {
            $activeLottery->append(['remaining_tickets', 'progress_percentage', 'time_remaining', 'participation_rate', 'is_ending_soon']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product' => new ProductResource($product),
                'lottery' => $activeLottery ? new LotteryResource($activeLottery) : null
            ]
        ]);
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
            
            // Ajouter les détails de la tombola active si elle existe
            if ($product->activeLottery) {
                $product->activeLottery->append(['remaining_tickets', 'progress_percentage', 'time_remaining', 'participation_rate', 'is_ending_soon']);
            }
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
                if ($product->activeLottery) {
                    $product->activeLottery->append(['remaining_tickets', 'progress_percentage', 'time_remaining', 'participation_rate', 'is_ending_soon']);
                }
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
}
