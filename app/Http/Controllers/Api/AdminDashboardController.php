<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Lottery;
use App\Models\Transaction;
use App\Models\LotteryTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        try {
            $now = Carbon::now();
            $lastMonth = $now->copy()->subMonth();
            
            // Users statistics
            $totalUsers = User::count();
            $activeUsers = User::where('last_login', '>=', $now->copy()->subDays(30))->count();
            $usersGrowth = $this->calculateGrowth(
                User::where('created_at', '>=', $lastMonth)->count(),
                User::where('created_at', '>=', $lastMonth->copy()->subMonth())
                    ->where('created_at', '<', $lastMonth)->count()
            );
            
            // Products statistics
            $totalProducts = Product::count();
            $activeProducts = Product::where('status', 'active')->count();
            $productsGrowth = $this->calculateGrowth(
                Product::where('created_at', '>=', $lastMonth)->count(),
                Product::where('created_at', '>=', $lastMonth->copy()->subMonth())
                    ->where('created_at', '<', $lastMonth)->count()
            );
            
            // Lotteries statistics
            $totalLotteries = Lottery::count();
            $activeLotteries = Lottery::where('status', 'active')->count();
            $lotteriesGrowth = $this->calculateGrowth(
                Lottery::where('created_at', '>=', $lastMonth)->count(),
                Lottery::where('created_at', '>=', $lastMonth->copy()->subMonth())
                    ->where('created_at', '<', $lastMonth)->count()
            );
            
            // Revenue statistics
            $totalRevenue = Transaction::where('status', 'completed')
                ->where('type', 'lottery_ticket')
                ->sum('amount');
            $monthlyRevenue = Transaction::where('status', 'completed')
                ->where('type', 'lottery_ticket')
                ->where('created_at', '>=', $now->copy()->startOfMonth())
                ->sum('amount');
            $lastMonthRevenue = Transaction::where('status', 'completed')
                ->where('type', 'lottery_ticket')
                ->where('created_at', '>=', $lastMonth->copy()->startOfMonth())
                ->where('created_at', '<', $now->copy()->startOfMonth())
                ->sum('amount');
            $revenueGrowth = $this->calculateGrowth($monthlyRevenue, $lastMonthRevenue);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'users' => [
                        'total' => $totalUsers,
                        'active' => $activeUsers,
                        'growth' => $usersGrowth
                    ],
                    'products' => [
                        'total' => $totalProducts,
                        'active' => $activeProducts,
                        'growth' => $productsGrowth
                    ],
                    'lotteries' => [
                        'total' => $totalLotteries,
                        'active' => $activeLotteries,
                        'growth' => $lotteriesGrowth
                    ],
                    'revenue' => [
                        'total' => $totalRevenue,
                        'monthly' => $monthlyRevenue,
                        'growth' => $revenueGrowth
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get recent lotteries
     */
    public function getRecentLotteries(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        try {
            $lotteries = Lottery::with(['product'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($lottery) {
                    return [
                        'id' => $lottery->id,
                        'lottery_number' => $lottery->lottery_number,
                        'status' => $lottery->status,
                        'sold_tickets' => $lottery->sold_tickets,
                        'total_tickets' => $lottery->total_tickets,
                        'end_date' => $lottery->end_date,
                        'created_at' => $lottery->created_at,
                        'product' => [
                            'id' => $lottery->product->id,
                            'name' => $lottery->product->name,
                            'image_url' => $lottery->product->image_url,
                            'main_image' => $lottery->product->main_image
                        ]
                    ];
                });
                
            return response()->json([
                'success' => true,
                'data' => $lotteries
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des tombolas récentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities(Request $request)
    {
        $limit = $request->get('limit', 15);
        
        try {
            $activities = collect();
            
            // Recent user registrations
            $recentUsers = User::latest()
                ->limit($limit / 3)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => 'user_' . $user->id,
                        'type' => 'user',
                        'title' => 'Nouvel utilisateur inscrit',
                        'description' => $user->first_name . ' ' . $user->last_name . ' a créé un compte',
                        'created_at' => $user->created_at
                    ];
                });
            
            // Recent products
            $recentProducts = Product::latest()
                ->limit($limit / 3)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => 'product_' . $product->id,
                        'type' => 'product',
                        'title' => 'Produit ajouté',
                        'description' => $product->name . ' a été publié',
                        'created_at' => $product->created_at
                    ];
                });
            
            // Recent transactions
            $recentTransactions = Transaction::where('status', 'completed')
                ->latest()
                ->limit($limit / 3)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => 'transaction_' . $transaction->id,
                        'type' => 'payment',
                        'title' => 'Paiement reçu',
                        'description' => number_format($transaction->amount) . ' FCFA pour un ticket de tombola',
                        'created_at' => $transaction->created_at
                    ];
                });
            
            // Merge and sort activities
            $activities = $recentUsers->concat($recentProducts)
                ->concat($recentTransactions)
                ->sortByDesc('created_at')
                ->take($limit)
                ->values();
            
            return response()->json([
                'success' => true,
                'data' => $activities
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des activités récentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get top performing products
     */
    public function getTopProducts(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        try {
            $products = Product::withCount(['lotteryTickets'])
                ->orderBy('lottery_tickets_count', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    // Calculate growth (simple mock for now)
                    $growth = rand(-10, 30);
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'total_tickets_sold' => $product->lottery_tickets_count,
                        'growth_percentage' => $growth,
                        'image_url' => $product->image_url,
                        'main_image' => $product->main_image
                    ];
                });
                
            return response()->json([
                'success' => true,
                'data' => $products
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des produits populaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
}