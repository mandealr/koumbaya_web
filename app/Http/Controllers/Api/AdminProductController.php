<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Lottery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    /**
     * Get all products with filters
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'merchant', 'activeLottery', 'latestLottery']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sale mode filter
        if ($request->filled('sale_mode')) {
            $query->where('sale_mode', $request->sale_mode);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $products = $query->paginate($perPage);

        // Transform products for admin view
        $products->getCollection()->transform(function ($product) {
            $lottery = $product->latestLottery;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'title' => $product->title, // Alias
                'description' => $product->description,
                'price' => $product->price,
                'ticket_price' => $product->ticket_price,
                'sale_mode' => $product->sale_mode,
                'status' => $product->status,
                'category_id' => $product->category_id,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ] : null,
                'merchant' => $product->merchant ? [
                    'id' => $product->merchant->id,
                    'name' => $product->merchant->first_name . ' ' . $product->merchant->last_name,
                    'email' => $product->merchant->email
                ] : null,
                'image_url' => $product->image_url,
                'main_image' => $product->main_image,
                'images' => $product->images,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                // Lottery info
                'has_lottery' => $product->has_active_lottery,
                'lottery' => $lottery ? [
                    'id' => $lottery->id,
                    'status' => $lottery->status,
                    'total_tickets' => $lottery->total_tickets,
                    'sold_tickets' => $lottery->sold_tickets,
                    'progress' => $lottery->total_tickets > 0 
                        ? round(($lottery->sold_tickets / $lottery->total_tickets) * 100)
                        : 0,
                    'end_date' => $lottery->end_date,
                    'draw_date' => $lottery->draw_date
                ] : null
            ];
        });

        // Get statistics
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'draft' => Product::where('is_active', false)->count(),
            'completed' => Product::where('is_active', true)->count(), // Products actifs considérés comme "complétés"
            'total_value' => Product::where('is_active', true)->sum('price'),
            'total_lottery_value' => Product::where('sale_mode', 'lottery')
                ->where('is_active', true)
                ->sum('price')
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
                'stats' => $stats,
                'categories' => $this->getCategories()
            ]
        ]);
    }

    /**
     * Get a single product
     */
    public function show($id)
    {
        $currentUser = auth()->user();
        
        // Vérifier que l'utilisateur est au moins Admin
        if (!$currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $product = Product::with([
                'category',
                'merchant.roles',
                'lotteries' => function ($query) {
                    $query->with(['winner', 'drawHistory'])
                          ->orderBy('created_at', 'desc');
                }
            ])
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }

        // Calculate detailed stats
        $totalTicketsSold = $product->lotteries->sum('sold_tickets');
        $totalRevenue = $product->lotteries->sum(function ($lottery) {
            return $lottery->sold_tickets * $lottery->ticket_price;
        });

        $stats = [
            'total_lotteries' => $product->lotteries->count(),
            'active_lotteries' => $product->lotteries->where('status', 'active')->count(),
            'completed_lotteries' => $product->lotteries->where('status', 'completed')->count(),
            'cancelled_lotteries' => $product->lotteries->where('status', 'cancelled')->count(),
            'total_tickets_sold' => $totalTicketsSold,
            'total_revenue' => $totalRevenue,
            'views_count' => $product->views_count ?? 0,
        ];

        // Format product data
        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'quantity' => $product->quantity,
            'status' => $product->status ?? ($product->is_active ? 'active' : 'inactive'),
            'type' => $product->type ?? 'physical',
            'sale_mode' => $product->sale_mode,
            'image_url' => $product->image_url,
            'main_image' => $product->main_image,
            'images' => $product->images,
            'views_count' => $product->views_count ?? 0,
            'is_active' => $product->is_active,
            'is_featured' => $product->is_featured,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'user' => [
                'id' => $product->merchant->id,
                'first_name' => $product->merchant->first_name,
                'last_name' => $product->merchant->last_name,
                'email' => $product->merchant->email,
                'phone' => $product->merchant->phone,
                'is_active' => $product->merchant->is_active,
                'roles' => $product->merchant->roles->pluck('name')->toArray(),
            ],
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'lotteries' => $product->lotteries->map(function ($lottery) {
                return [
                    'id' => $lottery->id,
                    'title' => $lottery->title,
                    'description' => $lottery->description,
                    'lottery_number' => $lottery->lottery_number,
                    'status' => $lottery->status,
                    'max_tickets' => $lottery->max_tickets,
                    'sold_tickets' => $lottery->sold_tickets,
                    'ticket_price' => $lottery->ticket_price,
                    'draw_date' => $lottery->draw_date,
                    'created_at' => $lottery->created_at,
                    'winner' => $lottery->winner ? [
                        'id' => $lottery->winner->id,
                        'first_name' => $lottery->winner->first_name,
                        'last_name' => $lottery->winner->last_name,
                    ] : null,
                    'winning_ticket_number' => $lottery->winning_ticket_number,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $productData,
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Update a product
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:1000',
            'ticket_price' => 'nullable|numeric|min:100',
            'sale_mode' => 'in:direct,lottery',
            'category_id' => 'exists:categories,id',
            'is_active' => 'boolean',
            'image' => 'nullable|string',
            'is_featured' => 'boolean',
            'vendor_profile_id' => 'nullable|exists:vendor_profiles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Si on modifie le profil vendeur ou le prix du ticket, valider
        if (($request->has('vendor_profile_id') || $request->has('ticket_price')) && $product->sale_mode === 'lottery') {
            $vendorProfileId = $request->vendor_profile_id ?? $product->vendor_profile_id;
            $ticketPrice = $request->ticket_price ?? $product->ticket_price;
            
            if ($vendorProfileId && $ticketPrice) {
                $vendor = \App\Models\VendorProfile::find($vendorProfileId);
                if ($vendor) {
                    $validation = \App\Services\TicketPriceCalculator::validateTicketPrice($ticketPrice, $vendor);
                    if (!$validation['is_valid']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Prix de ticket invalide selon les contraintes du profil vendeur',
                            'warnings' => $validation['warnings']
                        ], 422);
                    }
                }
            }
        }

        $product->update($request->only([
            'name', 'description', 'price', 'ticket_price', 
            'sale_mode', 'category_id', 'is_active', 'image', 'is_featured',
            'vendor_profile_id'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Produit mis à jour avec succès',
            'data' => ['product' => $product->load('category', 'merchant', 'vendorProfile')]
        ]);
    }

    /**
     * Delete a product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Check if product has active lottery
        if ($product->has_active_lottery) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un produit avec une tombola active'
            ], 422);
        }

        // Check if product has any paid tickets
        $hasPaidTickets = $product->lotteryTickets()
            ->where('status', 'paid')
            ->exists();

        if ($hasPaidTickets) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un produit avec des tickets vendus'
            ], 422);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit supprimé avec succès'
        ]);
    }

    /**
     * Update product status
     */
    public function updateStatus(Request $request, $id)
    {
        $currentUser = auth()->user();
        
        // Vérifier que l'utilisateur est au moins Admin
        if (!$currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,inactive,out_of_stock'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Si on désactive un produit avec une tombola active
        if ($request->status === 'inactive' && $product->has_active_lottery) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de désactiver un produit avec une tombola active'
            ], 422);
        }

        $product->update([
            'status' => $request->status,
            'is_active' => $request->status === 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Statut du produit mis à jour avec succès',
            'data' => ['product' => $product]
        ]);
    }

    /**
     * Get product statistics for admin dashboard
     */
    public function getStats()
    {
        $currentUser = auth()->user();
        
        // Vérifier que l'utilisateur est au moins Admin
        if (!$currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'inactive_products' => Product::where('is_active', false)->count(),
            'featured_products' => Product::where('is_featured', true)->count(),
            'lottery_products' => Product::where('sale_mode', 'lottery')->count(),
            'direct_products' => Product::where('sale_mode', 'direct')->count(),
            'total_lotteries' => Lottery::count(),
            'active_lotteries' => Lottery::where('status', 'active')->count(),
            'total_revenue' => Lottery::join('lottery_tickets', 'lotteries.id', '=', 'lottery_tickets.lottery_id')
                ->where('lottery_tickets.status', 'paid')
                ->sum(\DB::raw('lotteries.ticket_price')),
            'total_tickets_sold' => \App\Models\LotteryTicket::where('status', 'paid')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get available categories
     */
    private function getCategories()
    {
        return Category::where('is_active', true)
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();
    }
}