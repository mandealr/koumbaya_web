<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Lottery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $this->middleware('auth:api', ['except' => ['index', 'show', 'featured']]);
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
        $query = Product::active()->with(['category', 'merchant', 'activeLottery']);

        // Filtres
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->get('per_page', 15), 50);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'products' => $products
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/featured",
     *     tags={"Products"},
     *     summary="Produits mis en avant",
     *     @OA\Response(
     *         response=200,
     *         description="Produits featured",
     *         @OA\JsonContent(
     *             @OA\Property(property="products", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function featured()
    {
        $products = Product::active()
            ->featured()
            ->with(['category', 'merchant', 'activeLottery'])
            ->limit(10)
            ->get();

        return response()->json([
            'products' => $products
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

        return response()->json([
            'product' => $product
        ]);
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
     *             @OA\Property(property="min_participants", type="integer", example=300)
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
        $user = auth('api')->user();

        // Vérifier que l'utilisateur est un marchand
        if (!$user->is_merchant) {
            return response()->json(['error' => 'Seuls les marchands peuvent créer des produits'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:1000',
            'ticket_price' => 'required|numeric|min:100',
            'category_id' => 'required|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'string|url',
            'min_participants' => 'nullable|integer|min:300|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . time(),
            'description' => $request->description,
            'images' => $request->images,
            'price' => $request->price,
            'ticket_price' => $request->ticket_price,
            'min_participants' => $request->min_participants ?? 300,
            'category_id' => $request->category_id,
            'merchant_id' => $user->id,
            'status' => 'draft',
        ]);

        $product->load(['category', 'merchant']);

        return response()->json([
            'message' => 'Produit créé avec succès',
            'product' => $product
        ], 201);
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
        $user = auth('api')->user();
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($request->only([
            'name', 'description', 'price', 'ticket_price', 'images', 'status'
        ]));

        return response()->json([
            'message' => 'Produit mis à jour avec succès',
            'product' => $product->load(['category', 'merchant'])
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
        $user = auth('api')->user();
        $product = Product::findOrFail($id);

        if ($product->merchant_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if (!$product->canCreateLottery()) {
            return response()->json(['error' => 'Impossible de créer une tombola pour ce produit'], 422);
        }

        $validator = Validator::make($request->all(), [
            'duration_days' => 'required|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $totalTickets = (int) ceil($product->price / $product->ticket_price);
        
        $lottery = Lottery::create([
            'lottery_number' => 'KMB-' . date('Y') . '-' . str_pad($product->id, 6, '0', STR_PAD_LEFT),
            'product_id' => $product->id,
            'total_tickets' => $totalTickets,
            'ticket_price' => $product->ticket_price,
            'start_date' => now(),
            'end_date' => now()->addDays($request->duration_days),
            'status' => 'active',
        ]);

        // Mettre à jour le statut du produit
        $product->update(['status' => 'active']);

        return response()->json([
            'message' => 'Tombola créée avec succès',
            'lottery' => $lottery->load('product')
        ], 201);
    }
}
