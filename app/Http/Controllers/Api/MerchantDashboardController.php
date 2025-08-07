<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Merchant Dashboard",
 *     description="API endpoints pour le dashboard marchand"
 * )
 */
class MerchantDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'merchant']);
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/dashboard/stats",
     *     tags={"Merchant Dashboard"},
     *     summary="Statistiques générales du marchand",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques du dashboard",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_products", type="integer"),
     *                 @OA\Property(property="active_lotteries", type="integer"),
     *                 @OA\Property(property="total_sales", type="number"),
     *                 @OA\Property(property="tickets_sold", type="integer"),
     *                 @OA\Property(property="revenue_this_month", type="number"),
     *                 @OA\Property(property="growth_rate", type="number")
     *             )
     *         )
     *     )
     * )
     */
    public function getStats()
    {
        $merchantId = auth()->id();
        
        // Statistiques générales
        $totalProducts = Product::where('merchant_id', $merchantId)->count();
        $activeLotteries = Lottery::whereHas('product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })->where('status', 'active')->count();

        // Revenus et ventes
        $totalSales = $this->getTotalSales($merchantId);
        $ticketsSold = $this->getTicketsSold($merchantId);
        $revenueThisMonth = $this->getRevenueThisMonth($merchantId);
        $revenueLastMonth = $this->getRevenueLastMonth($merchantId);

        // Calcul du taux de croissance
        $growthRate = $revenueLastMonth > 0 
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : 0;

        return $this->sendResponse([
            'total_products' => $totalProducts,
            'active_lotteries' => $activeLotteries,
            'total_sales' => $totalSales,
            'tickets_sold' => $ticketsSold,
            'revenue_this_month' => $revenueThisMonth,
            'revenue_last_month' => $revenueLastMonth,
            'growth_rate' => round($growthRate, 2),
            'conversion_rate' => $this->getConversionRate($merchantId),
            'avg_ticket_price' => $this->getAverageTicketPrice($merchantId),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/dashboard/sales-chart",
     *     tags={"Merchant Dashboard"},
     *     summary="Données pour le graphique des ventes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Période (7d, 30d, 90d, 1y)",
     *         @OA\Schema(type="string", default="30d")
     *     ),
     *     @OA\Response(response=200, description="Données du graphique")
     * )
     */
    public function getSalesChart(Request $request)
    {
        $merchantId = auth()->id();
        $period = $request->get('period', '30d');
        
        $startDate = match($period) {
            '7d' => Carbon::now()->subDays(7),
            '30d' => Carbon::now()->subDays(30),
            '90d' => Carbon::now()->subDays(90),
            '1y' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30)
        };

        $salesData = Transaction::select(
            DB::raw('DATE(completed_at) as date'),
            DB::raw('SUM(amount) as revenue'),
            DB::raw('COUNT(*) as transactions'),
            DB::raw('SUM(quantity) as tickets')
        )
        ->whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->where('status', 'completed')
        ->where('completed_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return $this->sendResponse([
            'period' => $period,
            'data' => $salesData->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => floatval($item->revenue),
                    'transactions' => intval($item->transactions),
                    'tickets' => intval($item->tickets),
                ];
            }),
            'totals' => [
                'revenue' => $salesData->sum('revenue'),
                'transactions' => $salesData->sum('transactions'),
                'tickets' => $salesData->sum('tickets'),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/dashboard/top-products",
     *     tags={"Merchant Dashboard"},
     *     summary="Produits les plus vendus",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de produits",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(response=200, description="Liste des top produits")
     * )
     */
    public function getTopProducts(Request $request)
    {
        $merchantId = auth()->id();
        $limit = $request->get('limit', 10);

        $topProducts = Product::select([
            'products.*',
            DB::raw('COALESCE(SUM(transactions.amount), 0) as total_revenue'),
            DB::raw('COALESCE(SUM(transactions.quantity), 0) as tickets_sold'),
            DB::raw('COUNT(DISTINCT transactions.id) as total_transactions')
        ])
        ->leftJoin('lotteries', 'products.id', '=', 'lotteries.product_id')
        ->leftJoin('transactions', function ($join) {
            $join->on('lotteries.id', '=', 'transactions.lottery_id')
                 ->where('transactions.status', '=', 'completed');
        })
        ->where('products.merchant_id', $merchantId)
        ->with(['category', 'activeLottery'])
        ->groupBy('products.id')
        ->orderBy('total_revenue', 'desc')
        ->limit($limit)
        ->get();

        return $this->sendResponse([
            'products' => $topProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => $product->price,
                    'ticket_price' => $product->ticket_price,
                    'image_url' => $product->image_url,
                    'category' => $product->category->name ?? 'Non classé',
                    'total_revenue' => floatval($product->total_revenue),
                    'tickets_sold' => intval($product->tickets_sold),
                    'total_transactions' => intval($product->total_transactions),
                    'has_active_lottery' => $product->activeLottery !== null,
                    'lottery_status' => $product->activeLottery->status ?? null,
                    'conversion_rate' => $this->getProductConversionRate($product->id),
                ];
            })
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/dashboard/recent-transactions",
     *     tags={"Merchant Dashboard"},
     *     summary="Transactions récentes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de transactions",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(response=200, description="Transactions récentes")
     * )
     */
    public function getRecentTransactions(Request $request)
    {
        $merchantId = auth()->id();
        $limit = $request->get('limit', 20);

        $transactions = Transaction::with(['user', 'lottery.product'])
            ->whereHas('lottery.product', function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            })
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();

        return $this->sendResponse([
            'transactions' => $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'quantity' => $transaction->quantity,
                    'completed_at' => $transaction->completed_at,
                    'user' => [
                        'name' => $transaction->user->first_name . ' ' . $transaction->user->last_name,
                        'email' => $transaction->user->email,
                    ],
                    'product' => [
                        'title' => $transaction->lottery->product->title,
                        'lottery_number' => $transaction->lottery->lottery_number,
                    ],
                    'payment_method' => $transaction->payment_provider ?? 'Mobile Money',
                ];
            })
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/dashboard/lottery-performance",
     *     tags={"Merchant Dashboard"},
     *     summary="Performance des tombolas",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Performance des tombolas")
     * )
     */
    public function getLotteryPerformance()
    {
        $merchantId = auth()->id();

        $lotteries = Lottery::with(['product'])
            ->whereHas('product', function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            })
            ->select([
                'lotteries.*',
                DB::raw('COALESCE(lotteries.sold_tickets, 0) as tickets_sold'),
                DB::raw('(lotteries.sold_tickets / lotteries.total_tickets * 100) as completion_rate'),
                DB::raw('DATEDIFF(lotteries.end_date, NOW()) as days_remaining')
            ])
            ->orderBy('completion_rate', 'desc')
            ->get();

        return $this->sendResponse([
            'lotteries' => $lotteries->map(function ($lottery) {
                return [
                    'id' => $lottery->id,
                    'lottery_number' => $lottery->lottery_number,
                    'product_title' => $lottery->product->title,
                    'status' => $lottery->status,
                    'total_tickets' => $lottery->total_tickets,
                    'sold_tickets' => $lottery->sold_tickets,
                    'completion_rate' => round($lottery->completion_rate, 2),
                    'days_remaining' => max(0, $lottery->days_remaining),
                    'ticket_price' => $lottery->ticket_price,
                    'total_revenue' => $lottery->sold_tickets * $lottery->ticket_price,
                    'start_date' => $lottery->start_date,
                    'end_date' => $lottery->end_date,
                    'is_ending_soon' => $lottery->is_ending_soon,
                ];
            }),
            'summary' => [
                'total_lotteries' => $lotteries->count(),
                'active_lotteries' => $lotteries->where('status', 'active')->count(),
                'completed_lotteries' => $lotteries->where('status', 'completed')->count(),
                'avg_completion_rate' => round($lotteries->avg('completion_rate'), 2),
            ]
        ]);
    }

    /**
     * Méthodes utilitaires privées
     */
    private function getTotalSales($merchantId)
    {
        return Transaction::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->where('status', 'completed')
        ->sum('amount');
    }

    private function getTicketsSold($merchantId)
    {
        return Transaction::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->where('status', 'completed')
        ->sum('quantity');
    }

    private function getRevenueThisMonth($merchantId)
    {
        return Transaction::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->where('status', 'completed')
        ->whereBetween('completed_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])
        ->sum('amount');
    }

    private function getRevenueLastMonth($merchantId)
    {
        return Transaction::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->where('status', 'completed')
        ->whereBetween('completed_at', [
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth()
        ])
        ->sum('amount');
    }

    private function getConversionRate($merchantId)
    {
        $totalViews = Product::where('merchant_id', $merchantId)->sum('views_count') ?: 1;
        $totalSales = $this->getTicketsSold($merchantId);
        
        return round(($totalSales / $totalViews) * 100, 2);
    }

    private function getAverageTicketPrice($merchantId)
    {
        return Product::where('merchant_id', $merchantId)
            ->avg('ticket_price') ?: 0;
    }

    private function getProductConversionRate($productId)
    {
        // Simplified conversion rate calculation
        $product = Product::find($productId);
        $views = $product->views_count ?? 1;
        $sales = Transaction::whereHas('lottery', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })
        ->where('status', 'completed')
        ->sum('quantity');
        
        return round(($sales / $views) * 100, 2);
    }
}