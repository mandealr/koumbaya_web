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
                    'progress' => round(($lottery->sold_tickets / $lottery->total_tickets) * 100),
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
        $product = Product::with([
            'category', 
            'merchant', 
            'lotteries',
            'lotteryTickets'
        ])->findOrFail($id);

        $stats = [
            'total_lotteries' => $product->lotteries()->count(),
            'completed_lotteries' => $product->completedLotteries()->count(),
            'total_tickets_sold' => $product->lotteryTickets()->where('status', 'paid')->count(),
            'total_revenue' => $product->transactions()->where('status', 'completed')->sum('amount')
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
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
            'is_featured' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->only([
            'name', 'description', 'price', 'ticket_price', 
            'sale_mode', 'category_id', 'is_active', 'image', 'is_featured'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Produit mis à jour avec succès',
            'data' => ['product' => $product->load('category', 'merchant')]
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