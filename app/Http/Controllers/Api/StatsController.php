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
        $productStats = Product::where('merchant_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_products,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_products,
                SUM(views_count) as total_views
            ')
            ->first();
            
        // Statistiques des commandes
        $orderStats = Order::where('type', 'lottery')
            ->whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
            })
            ->where('status', 'paid')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue
            ')
            ->first();
            
        // Statistiques des tombolas
        $lotteryStats = Lottery::whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
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
                $query->where('merchant_id', $user->id);
            })
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonth())
            ->sum('total_amount');
            
        $weeklyRevenue = Order::where('type', 'lottery')
            ->whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
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
        
        $query = Product::where('merchant_id', $user->id);
        
        if ($productId) {
            $query->where('id', $productId);
        }
        
        // Pour sales_count, nous devons compter les commandes
        $stats = $query->selectRaw('
            COUNT(*) as total_products,
            SUM(views_count) as total_views
        ')
        ->first();
        
        // Calculer le total de ventes via les commandes
        $salesData = Order::whereHas('product', function($q) use ($user) {
                $q->where('merchant_id', $user->id);
            })
            ->where('status', 'paid')
            ->selectRaw('COUNT(*) as total_sales')
            ->first();
            
        // Top performing products basé sur les revenus
        $topProducts = Product::where('merchant_id', $user->id)
            ->withCount(['orders as order_count' => function($query) {
                $query->where('status', 'paid');
            }])
            ->withSum(['orders as revenue' => function($query) {
                $query->where('status', 'paid');
            }], 'total_amount')
            ->having('order_count', '>', 0)
            ->orderBy('revenue', 'desc')
            ->take(5)
            ->get(['id', 'name', 'views_count', 'price']);
            
        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_products' => (int) ($stats->total_products ?? 0),
                    'total_views' => (int) ($stats->total_views ?? 0),
                    'total_sales' => (int) ($salesData->total_sales ?? 0),
                    'avg_conversion_rate' => $stats->total_views > 0 ? 
                        round(($salesData->total_sales / $stats->total_views) * 100, 2) : 0
                ],
                'top_products' => $topProducts->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sales' => $product->order_count,
                        'views' => $product->views_count,
                        'conversion_rate' => $product->views_count > 0 ? 
                            round(($product->order_count / $product->views_count) * 100, 2) : 0,
                        'revenue' => $product->revenue ?? 0
                    ];
                })
            ]
        ]);
    }
    
    /**
     * Get merchant orders statistics
     */
    public function merchantOrders(Request $request)
    {
        $user = Auth::user();
        
        // Statistiques des commandes par statut
        $orderStats = Order::where('type', 'lottery')
            ->whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
            })
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_orders,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END) as total_revenue,
                AVG(CASE WHEN status = "paid" THEN total_amount ELSE NULL END) as avg_order_value
            ')
            ->first();
            
        // Commandes récentes
        $recentOrders = Order::where('type', 'lottery')
            ->whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
            })
            ->with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_name' => $order->user->first_name . ' ' . $order->user->last_name,
                    'product_name' => $order->product->name,
                    'amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at
                ];
            });
            
        // Revenus par mois (12 derniers mois)
        $monthlyRevenue = Order::where('type', 'lottery')
            ->whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
            })
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subYear())
            ->selectRaw('MONTH(paid_at) as month, YEAR(paid_at) as year, SUM(total_amount) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_orders' => (int) ($orderStats->total_orders ?? 0),
                    'paid_orders' => (int) ($orderStats->paid_orders ?? 0),
                    'pending_orders' => (int) ($orderStats->pending_orders ?? 0),
                    'cancelled_orders' => (int) ($orderStats->cancelled_orders ?? 0),
                    'total_revenue' => (float) ($orderStats->total_revenue ?? 0),
                    'avg_order_value' => (float) ($orderStats->avg_order_value ?? 0)
                ],
                'recent_orders' => $recentOrders,
                'monthly_revenue' => $monthlyRevenue
            ]
        ]);
    }
    
    /**
     * Get merchant lotteries statistics
     */
    public function merchantLotteries(Request $request)
    {
        $user = Auth::user();
        
        // Statistiques des tombolas
        $lotteryStats = Lottery::whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
            })
            ->selectRaw('
                COUNT(*) as total_lotteries,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_lotteries,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_lotteries,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lotteries,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_lotteries,
                SUM(sold_tickets) as total_tickets_sold,
                SUM(max_tickets) as total_tickets_available,
                AVG(CASE WHEN status = "completed" AND max_tickets > 0 THEN (sold_tickets / max_tickets) * 100 ELSE NULL END) as avg_completion_rate
            ')
            ->first();
            
        // Tombolas actives avec possibilité de tirage
        $drawableLotteries = Lottery::whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
            })
            ->where('status', 'active')
            ->whereNull('winning_ticket_number') // Pas encore tirée
            ->where(function($q) {
                $q->where('draw_date', '<=', now())
                  ->orWhereRaw('sold_tickets >= max_tickets');
            })
            ->count();
            
        // Performance des tombolas récentes
        $recentLotteries = Lottery::whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
            })
            ->with(['product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($lottery) {
                return [
                    'id' => $lottery->id,
                    'lottery_number' => $lottery->lottery_number,
                    'product_name' => $lottery->product->name,
                    'status' => $lottery->status,
                    'sold_tickets' => $lottery->sold_tickets ?? 0,
                    'total_tickets' => $lottery->max_tickets ?? 0,
                    'completion_rate' => $lottery->max_tickets > 0 ? 
                        round(($lottery->sold_tickets / $lottery->max_tickets) * 100, 2) : 0,
                    'draw_date' => $lottery->draw_date,
                    'created_at' => $lottery->created_at
                ];
            });
            
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_lotteries' => (int) ($lotteryStats->total_lotteries ?? 0),
                    'active_lotteries' => (int) ($lotteryStats->active_lotteries ?? 0),
                    'pending_lotteries' => (int) ($lotteryStats->pending_lotteries ?? 0),
                    'completed_lotteries' => (int) ($lotteryStats->completed_lotteries ?? 0),
                    'cancelled_lotteries' => (int) ($lotteryStats->cancelled_lotteries ?? 0),
                    'total_tickets_sold' => (int) ($lotteryStats->total_tickets_sold ?? 0),
                    'total_tickets_available' => (int) ($lotteryStats->total_tickets_available ?? 0),
                    'avg_completion_rate' => (float) ($lotteryStats->avg_completion_rate ?? 0),
                    'drawable_lotteries' => $drawableLotteries
                ],
                'recent_lotteries' => $recentLotteries
            ]
        ]);
    }
    
    /**
     * Get comprehensive merchant analytics
     */
    public function merchantAnalytics(Request $request)
    {
        $user = Auth::user();
        $period = $request->input('period', '30d'); // 7d, 30d, 90d, 1y
        
        // Calculer la date de début selon la période
        $startDate = match($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30)
        };
        
        // Revenus par jour pour la période
        $dailyRevenue = Order::where('type', 'lottery')
            ->whereHas('product', function($query) use ($user) {
                $query->where('merchant_id', $user->id);
            })
            ->where('status', 'paid')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Top produits par revenus
        $topProducts = Product::where('merchant_id', $user->id)
            ->withSum(['orders as revenue' => function($query) use ($startDate) {
                $query->where('status', 'paid')
                      ->where('paid_at', '>=', $startDate);
            }], 'total_amount')
            ->withCount(['orders as order_count' => function($query) use ($startDate) {
                $query->where('status', 'paid')
                      ->where('paid_at', '>=', $startDate);
            }])
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'price', 'image'])
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->image,
                    'revenue' => $product->revenue ?? 0,
                    'order_count' => $product->order_count ?? 0
                ];
            });
            
        // Statistiques de conversion
        $conversionStats = Product::where('merchant_id', $user->id)
            ->selectRaw('
                SUM(views_count) as total_views
            ')
            ->first();
            
        // Calculer le total de ventes via les commandes
        $totalSales = Order::whereHas('product', function($q) use ($user) {
                $q->where('merchant_id', $user->id);
            })
            ->where('status', 'paid')
            ->count();
            
        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'daily_revenue' => $dailyRevenue,
                'top_products' => $topProducts,
                'conversion' => [
                    'total_views' => (int) ($conversionStats->total_views ?? 0),
                    'total_sales' => (int) $totalSales,
                    'avg_conversion_rate' => $conversionStats->total_views > 0 ? 
                        round(($totalSales / $conversionStats->total_views) * 100, 2) : 0
                ]
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
                'total_tickets' => $lottery->max_tickets,
                'ticket_price' => $lottery->ticket_price,
                'draw_date' => $lottery->draw_date,
                'progress' => $lottery->max_tickets > 0 ? 
                    round(($lottery->sold_tickets / $lottery->max_tickets) * 100, 2) : 0,
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
        $activeProducts = Product::where('is_active', 1)->count();
        
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