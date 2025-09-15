<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\LotteryTicket;
use App\Models\Lottery;
use App\Models\Product;

class StatsController extends Controller
{
    /**
     * Get customer dashboard statistics
     */
    public function customerDashboard(Request $request)
    {
        $user = Auth::user();
        
        // Calculer les statistiques des tickets
        $ticketStats = LotteryTicket::where('user_id', $user->id)
            ->where('status', 'paid')
            ->selectRaw('
                COUNT(*) as total_tickets,
                COUNT(DISTINCT lottery_id) as lotteries_participated,
                SUM(price) as total_spent,
                SUM(CASE WHEN is_winner = 1 THEN 1 ELSE 0 END) as prizes_won
            ')
            ->first();
            
        // Tickets actifs (tombolas non encore tirées)
        $activeTickets = LotteryTicket::where('user_id', $user->id)
            ->where('status', 'paid')
            ->whereHas('lottery', function($query) {
                $query->where('status', 'active')
                    ->orWhere('status', 'pending');
            })
            ->count();
            
        return response()->json([
            'success' => true,
            'data' => [
                'total_tickets' => (int) ($ticketStats->total_tickets ?? 0),
                'lotteries_participated' => (int) ($ticketStats->lotteries_participated ?? 0),
                'total_spent' => (float) ($ticketStats->total_spent ?? 0),
                'prizes_won' => (int) ($ticketStats->prizes_won ?? 0),
                'active_tickets' => $activeTickets
            ]
        ]);
    }
    
    /**
     * Get customer tickets page statistics
     */
    public function customerTickets(Request $request)
    {
        $user = Auth::user();
        
        // Statistiques par statut
        $statusStats = LotteryTicket::where('user_id', $user->id)
            ->where('status', 'paid')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_winner = 1 THEN 1 ELSE 0 END) as won,
                SUM(CASE WHEN is_winner = 0 AND lottery_id IN (
                    SELECT id FROM lotteries WHERE status = "completed"
                ) THEN 1 ELSE 0 END) as lost,
                SUM(price) as total_spent
            ')
            ->first();
            
        // Tickets actifs
        $activeCount = LotteryTicket::where('user_id', $user->id)
            ->where('status', 'paid')
            ->whereHas('lottery', function($query) {
                $query->whereIn('status', ['active', 'pending']);
            })
            ->count();
            
        return response()->json([
            'success' => true,
            'data' => [
                'total_tickets' => (int) ($statusStats->total ?? 0),
                'prizes_won' => (int) ($statusStats->won ?? 0),
                'total_spent' => (float) ($statusStats->total_spent ?? 0),
                'active_tickets' => $activeCount
            ]
        ]);
    }
    
    /**
     * Get merchant dashboard statistics
     */
    public function merchantDashboard(Request $request)
    {
        $user = Auth::user();
        
        // Statistiques des produits
        $productStats = Product::where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_products,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_products,
                SUM(views_count) as total_views
            ')
            ->first();
            
        // Statistiques des commandes
        $orderStats = Order::where('type', 'lottery')
            ->whereHas('product', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'paid')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue
            ')
            ->first();
            
        // Statistiques des tombolas
        $lotteryStats = Lottery::whereHas('product', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->selectRaw('
                COUNT(*) as total_lotteries,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_lotteries,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lotteries,
                SUM(sold_tickets) as total_tickets_sold
            ')
            ->first();
            
        // Revenus par période
        $monthlyRevenue = Order::where('type', 'lottery')
            ->whereHas('product', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonth())
            ->sum('total_amount');
            
        $weeklyRevenue = Order::where('type', 'lottery')
            ->whereHas('product', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subWeek())
            ->sum('total_amount');
            
        return response()->json([
            'success' => true,
            'data' => [
                'products' => [
                    'total' => (int) ($productStats->total_products ?? 0),
                    'active' => (int) ($productStats->active_products ?? 0),
                    'total_views' => (int) ($productStats->total_views ?? 0)
                ],
                'orders' => [
                    'total' => (int) ($orderStats->total_orders ?? 0),
                    'total_revenue' => (float) ($orderStats->total_revenue ?? 0)
                ],
                'lotteries' => [
                    'total' => (int) ($lotteryStats->total_lotteries ?? 0),
                    'active' => (int) ($lotteryStats->active_lotteries ?? 0),
                    'completed' => (int) ($lotteryStats->completed_lotteries ?? 0),
                    'total_tickets_sold' => (int) ($lotteryStats->total_tickets_sold ?? 0)
                ],
                'revenue' => [
                    'monthly' => (float) $monthlyRevenue,
                    'weekly' => (float) $weeklyRevenue,
                    'total' => (float) ($orderStats->total_revenue ?? 0)
                ]
            ]
        ]);
    }
    
    /**
     * Get merchant product statistics
     */
    public function merchantProducts(Request $request)
    {
        $user = Auth::user();
        $productId = $request->input('product_id');
        
        $query = Product::where('user_id', $user->id);
        
        if ($productId) {
            $query->where('id', $productId);
        }
        
        $stats = $query->selectRaw('
            COUNT(*) as total_products,
            SUM(views_count) as total_views,
            SUM(sales_count) as total_sales,
            AVG(CASE WHEN views_count > 0 THEN (sales_count / views_count) * 100 ELSE 0 END) as avg_conversion_rate
        ')
        ->first();
        
        // Top performing products
        $topProducts = Product::where('user_id', $user->id)
            ->where('sales_count', '>', 0)
            ->orderBy('sales_count', 'desc')
            ->take(5)
            ->get(['id', 'name', 'sales_count', 'views_count', 'price']);
            
        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_products' => (int) ($stats->total_products ?? 0),
                    'total_views' => (int) ($stats->total_views ?? 0),
                    'total_sales' => (int) ($stats->total_sales ?? 0),
                    'avg_conversion_rate' => round((float) ($stats->avg_conversion_rate ?? 0), 2)
                ],
                'top_products' => $topProducts->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sales' => $product->sales_count,
                        'views' => $product->views_count,
                        'conversion_rate' => $product->views_count > 0 ? 
                            round(($product->sales_count / $product->views_count) * 100, 2) : 0,
                        'revenue' => $product->sales_count * $product->price
                    ];
                })
            ]
        ]);
    }
    
    /**
     * Get popular active lotteries
     */
    public function popularLotteries(Request $request)
    {
        $limit = $request->input('limit', 5);
        
        // Récupérer les tombolas actives triées par nombre de tickets vendus
        $popularLotteries = Lottery::where('status', 'active')
            ->with(['product.category', 'product.merchant'])
            ->orderBy('sold_tickets', 'desc')
            ->orderBy('created_at', 'desc') // En cas d'égalité, prendre les plus récentes
            ->limit($limit)
            ->get();
            
        // Transformer les données pour le frontend
        $lotteries = $popularLotteries->map(function($lottery) {
            return [
                'id' => $lottery->id,
                'lottery_number' => $lottery->lottery_number,
                'title' => $lottery->title,
                'sold_tickets' => $lottery->sold_tickets,
                'total_tickets' => $lottery->total_tickets ?? $lottery->max_tickets,
                'ticket_price' => $lottery->ticket_price,
                'draw_date' => $lottery->draw_date ?? $lottery->end_date,
                'progress' => $lottery->total_tickets > 0 ? 
                    round(($lottery->sold_tickets / $lottery->total_tickets) * 100, 2) : 0,
                'time_remaining' => $lottery->time_remaining ?? null,
                'product' => [
                    'id' => $lottery->product->id,
                    'name' => trim(str_replace(["\r\n", "\r", "\n"], ' ', $lottery->product->name)),
                    'price' => $lottery->product->price,
                    'image_url' => $lottery->product->image_url ?? $lottery->product->main_image ?? $lottery->product->image,
                    'category' => $lottery->product->category ? [
                        'id' => $lottery->product->category->id,
                        'name' => $lottery->product->category->name
                    ] : null
                ]
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $lotteries
        ]);
    }
    
    /**
     * Get platform-wide statistics (admin only)
     */
    public function platformStats(Request $request)
    {
        // Vérifier si l'utilisateur est admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }
        
        // Total users
        $totalUsers = DB::table('users')->count();
        $activeUsers = DB::table('users')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();
            
        // Total products
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->count();
        
        // Total lotteries
        $totalLotteries = Lottery::count();
        $activeLotteries = Lottery::where('status', 'active')->count();
        
        // Total revenue
        $totalRevenue = Order::where('status', 'paid')->sum('total_amount');
        $monthlyRevenue = Order::where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonth())
            ->sum('total_amount');
            
        // Total tickets
        $totalTickets = LotteryTicket::where('status', 'paid')->count();
        $monthlyTickets = LotteryTicket::where('status', 'paid')
            ->where('created_at', '>=', now()->subMonth())
            ->count();
            
        return response()->json([
            'success' => true,
            'data' => [
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers
                ],
                'products' => [
                    'total' => $totalProducts,
                    'active' => $activeProducts
                ],
                'lotteries' => [
                    'total' => $totalLotteries,
                    'active' => $activeLotteries
                ],
                'revenue' => [
                    'total' => (float) $totalRevenue,
                    'monthly' => (float) $monthlyRevenue
                ],
                'tickets' => [
                    'total' => $totalTickets,
                    'monthly' => $monthlyTickets
                ]
            ]
        ]);
    }
}