<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\Payment;
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

        // Compter les commandes en attente
        $pendingOrders = Payment::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })->where('status', 'pending')->count();

        return $this->sendResponse([
            'total_products' => intval($totalProducts ?? 0),
            'active_lotteries' => intval($activeLotteries ?? 0),
            'total_sales' => floatval($totalSales ?? 0),
            'tickets_sold' => intval($ticketsSold ?? 0),
            'revenue_this_month' => floatval($revenueThisMonth ?? 0),
            'revenue_last_month' => floatval($revenueLastMonth ?? 0),
            'growth_rate' => floatval(round($growthRate, 2)),
            'conversion_rate' => floatval($this->getConversionRate($merchantId)),
            'avg_ticket_price' => floatval($this->getAverageTicketPrice($merchantId)),
            'pending_orders' => intval($pendingOrders ?? 0),
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

        $salesData = Payment::select(
            DB::raw('DATE(paid_at) as date'),
            DB::raw('SUM(amount) as revenue'),
            DB::raw('COUNT(*) as transactions'),
            DB::raw('COUNT(*) as tickets')
        )
        ->whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->whereIn('status', ['completed', 'paid'])
        ->where('paid_at', '>=', $startDate)
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
        try {
            $merchantId = auth()->id();
            $limit = $request->get('limit', 10);

            $topProducts = Product::select([
                'products.id',
                'products.name', 
                'products.price',
                'products.image',
                'products.created_at',
                'products.updated_at',
                'products.merchant_id',
                'products.category_id',
                'products.description',
                'products.is_active',
                'products.views_count',
                'products.sale_mode',
                'products.meta',
                'products.currency',
                'products.stock_quantity',
                'products.is_featured',
                DB::raw('COALESCE(SUM(payments.amount), 0) as total_revenue'),
                DB::raw('COALESCE(COUNT(DISTINCT payments.id), 0) as tickets_sold'),
                DB::raw('COUNT(DISTINCT payments.id) as total_transactions')
            ])
            ->leftJoin('lotteries', 'products.id', '=', 'lotteries.product_id')
            ->leftJoin('payments', function ($join) {
                $join->on('lotteries.id', '=', 'payments.lottery_id')
                     ->where('payments.status', '=', 'completed');
            })
            ->where('products.merchant_id', $merchantId)
            ->groupBy(
                'products.id',
                'products.name', 
                'products.price',
                'products.image',
                'products.created_at',
                'products.updated_at',
                'products.merchant_id',
                'products.category_id',
                'products.description',
                'products.is_active',
                'products.views_count',
                'products.sale_mode',
                'products.meta',
                'products.currency',
                'products.stock_quantity',
                'products.is_featured'
            )
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();

            // Charger les relations après la requête pour éviter les conflits avec GROUP BY
            $topProducts->load(['category']);

            $productsData = $topProducts->map(function ($product) {
                try {
                    // Vérifier s'il y a une tombola active pour ce produit
                    $activeLottery = Lottery::where('product_id', $product->id)
                        ->where('status', 'active')
                        ->first();

                    // Récupérer le ticket_price de façon sécurisée
                    $ticketPrice = 0;
                    if ($product->meta && is_array($product->meta)) {
                        $ticketPrice = $product->meta['ticket_price'] ?? 0;
                    } elseif ($product->meta && is_string($product->meta)) {
                        $meta = json_decode($product->meta, true);
                        $ticketPrice = $meta['ticket_price'] ?? 0;
                    }

                    // Utiliser l'accesseur image_url du modèle Product qui gère la conversion automatique
                    $imageUrl = $product->image_url;

                    return [
                        'id' => $product->id,
                        'title' => $product->name ?? 'Produit sans titre',
                        'name' => $product->name ?? 'Produit sans titre',
                        'price' => floatval($product->price ?? 0),
                        'ticket_price' => floatval($ticketPrice),
                        'image_url' => $imageUrl,
                        'category' => $product->category->name ?? 'Non classé',
                        'total_revenue' => floatval($product->total_revenue ?? 0),
                        'tickets_sold' => intval($product->tickets_sold ?? 0),
                        'total_transactions' => intval($product->total_transactions ?? 0),
                        'has_active_lottery' => $activeLottery !== null,
                        'lottery_status' => $activeLottery ? $activeLottery->status : null,
                        'conversion_rate' => $this->getProductConversionRate($product->id),
                    ];
                } catch (\Exception $e) {
                    \Log::warning("Erreur lors du traitement du produit {$product->id}: " . $e->getMessage());
                    
                    // Retourner des données par défaut en cas d'erreur
                    return [
                        'id' => $product->id,
                        'title' => $product->name ?? 'Produit sans titre',
                        'name' => $product->name ?? 'Produit sans titre',
                        'price' => floatval($product->price ?? 0),
                        'ticket_price' => 0,
                        'image_url' => null,
                        'category' => 'Non classé',
                        'total_revenue' => floatval($product->total_revenue ?? 0),
                        'tickets_sold' => intval($product->tickets_sold ?? 0),
                        'total_transactions' => intval($product->total_transactions ?? 0),
                        'has_active_lottery' => false,
                        'lottery_status' => null,
                        'conversion_rate' => 0,
                    ];
                }
            });

            return $this->sendResponse($productsData->toArray());

        } catch (\Exception $e) {
            \Log::error('Erreur dans getTopProducts: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return $this->sendResponse([]);
        }
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
        try {
            $merchantId = auth()->id();
            $limit = $request->get('limit', 20);
            
            $query = Payment::with(['user', 'lottery.product', 'lottery_tickets'])
                ->whereHas('lottery.product', function ($query) use ($merchantId) {
                    $query->where('merchant_id', $merchantId);
                });
        
        // Filtres
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('lottery.product', function ($productQuery) use ($search) {
                      $productQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        } else {
            // Par défaut, on montre toutes les transactions complétées
            $query->whereIn('status', ['completed', 'pending', 'confirmed', 'cancelled']);
        }
        
        if ($request->has('product_id') && $request->product_id) {
            $query->whereHas('lottery.product', function ($q) use ($request) {
                $q->where('id', $request->product_id);
            });
        }
        
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $transactions = $query->orderBy('created_at', 'desc')
                             ->limit($limit)
                             ->get();

            $transactionsData = $transactions->map(function ($transaction) {
                // Récupérer les numéros de tickets de manière sécurisée
                $ticketNumbers = [];
                if ($transaction->lottery_tickets && $transaction->lottery_tickets->count() > 0) {
                    $ticketNumbers = $transaction->lottery_tickets->pluck('ticket_number')->toArray();
                }
                
                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id ?? '',
                    'amount' => floatval($transaction->amount ?? 0),
                    'quantity' => intval($transaction->quantity ?? 0),
                    'ticket_numbers' => $ticketNumbers,
                    'status' => $transaction->status ?? 'unknown',
                    'completed_at' => $transaction->paid_at ?? $transaction->created_at,
                    'user' => [
                        'name' => ($transaction->user ? 
                            ($transaction->user->first_name . ' ' . $transaction->user->last_name) : 
                            'Utilisateur inconnu'),
                        'email' => $transaction->user->email ?? '',
                    ],
                    'product' => [
                        'title' => $transaction->lottery->product->title ?? 'Produit non trouvé',
                        'image_url' => $transaction->lottery->product->images ?? null,
                        'lottery_number' => $transaction->lottery->lottery_number ?? '',
                    ],
                    'payment_method' => $transaction->payment_provider ?? 'Mobile Money',
                ];
            });

            return $this->sendResponse($transactionsData->toArray());

        } catch (\Exception $e) {
            \Log::error('Erreur dans getRecentTransactions: ' . $e->getMessage());
            return $this->sendResponse([]);
        }
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
    public function getLotteryPerformance(Request $request)
    {
        $merchantId = auth()->id();
        
        // Si c'est une requête pour la liste des tombolas (avec pagination)
        if ($request->has('per_page') || $request->has('page')) {
            return $this->getLotteriesForList($request);
        }

        $lotteries = Lottery::with(['product'])
            ->whereHas('product', function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            })
            ->select([
                'lotteries.*',
                DB::raw('COALESCE(lotteries.sold_tickets, 0) as tickets_sold'),
                DB::raw('(lotteries.sold_tickets / lotteries.max_tickets * 100) as completion_rate'),
                DB::raw('DATEDIFF(lotteries.draw_date, NOW()) as days_remaining')
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
                    'total_tickets' => $lottery->max_tickets,
                    'sold_tickets' => $lottery->sold_tickets,
                    'completion_rate' => round($lottery->completion_rate, 2),
                    'days_remaining' => max(0, $lottery->days_remaining),
                    'ticket_price' => $lottery->ticket_price,
                    'total_revenue' => $lottery->sold_tickets * $lottery->ticket_price,
                    'start_date' => $lottery->start_date,
                    'end_date' => $lottery->draw_date,
                    'is_ending_soon' => $lottery->is_ending_soon,
                ];
            }),
            'summary' => [
                'total_lotteries' => $lotteries->count(),
                'active_lotteries' => $lotteries->where('status', 'active')->count(),
                'completed_lotteries' => $lotteries->whereIn('status', ['completed', 'paid'])->count(),
                'avg_completion_rate' => round($lotteries->avg('completion_rate'), 2),
            ]
        ]);
    }

    /**
     * Méthodes utilitaires privées
     */
    private function getTotalSales($merchantId)
    {
        return Payment::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->whereIn('status', ['completed', 'paid'])
        ->sum('amount') ?: 0;
    }

    private function getTicketsSold($merchantId)
    {
        return Payment::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->whereIn('status', ['completed', 'paid'])
        ->count() ?: 0;
    }

    private function getRevenueThisMonth($merchantId)
    {
        return Payment::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->whereIn('status', ['completed', 'paid'])
        ->whereBetween('paid_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])
        ->sum('amount') ?: 0;
    }

    private function getRevenueLastMonth($merchantId)
    {
        return Payment::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->whereIn('status', ['completed', 'paid'])
        ->whereBetween('paid_at', [
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth()
        ])
        ->sum('amount') ?: 0;
    }

    private function getConversionRate($merchantId)
    {
        $totalViews = Product::where('merchant_id', $merchantId)->sum('views_count') ?: 1;
        $totalSales = $this->getTicketsSold($merchantId);
        
        return round(($totalSales / $totalViews) * 100, 2);
    }

    private function getAverageTicketPrice($merchantId)
    {
        $products = Product::where('merchant_id', $merchantId)->get();
        if ($products->isEmpty()) return 0;
        
        $totalTicketPrice = $products->sum(function($product) {
            return $product->ticket_price ?? 0;
        });
        
        return round($totalTicketPrice / $products->count(), 2);
    }

    private function getProductConversionRate($productId)
    {
        // Simplified conversion rate calculation
        $product = Product::find($productId);
        $views = $product->views_count ?? 0;
        $sales = Payment::whereHas('lottery', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })
        ->whereIn('status', ['completed', 'paid'])
        ->count();
        
        // Prevent division by zero
        if ($views == 0) {
            return 0;
        }
        
        return round(($sales / $views) * 100, 2);
    }
    
    /**
     * Update transaction status
     */
    public function updateTransactionStatus(Request $request, $id)
    {
        $merchantId = auth()->id();
        
        // Validation
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);
        
        // Vérifier que la transaction appartient au marchand
        $transaction = Payment::whereHas('lottery.product', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })->find($id);
        
        if (!$transaction) {
            return $this->sendError('Transaction non trouvée', 404);
        }
        
        // Vérifier les transitions de statut autorisées
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => [], // Aucune transition autorisée
            'cancelled' => []  // Aucune transition autorisée
        ];
        
        if (!in_array($request->status, $allowedTransitions[$transaction->status] ?? [])) {
            return $this->sendError('Transition de statut non autorisée', 400);
        }
        
        // Mettre à jour le statut
        $transaction->status = $request->status;
        
        if ($request->status === 'completed' && !$transaction->paid_at) {
            $transaction->paid_at = now();
        }
        
        $transaction->save();
        
        return $this->sendResponse([
            'message' => 'Statut mis à jour avec succès',
            'transaction' => [
                'id' => $transaction->id,
                'status' => $transaction->status
            ]
        ]);
    }
    
    /**
     * Export orders to CSV
     */
    public function exportOrders(Request $request)
    {
        $merchantId = auth()->id();
        $format = $request->get('format', 'csv');
        
        // Pour l'instant, on retourne juste un message
        // Dans une vraie implémentation, on générerait le fichier et retournerait l'URL de téléchargement
        return $this->sendResponse([
            'message' => 'Export en cours de traitement',
            'format' => $format,
            'status' => 'processing'
        ]);
    }

    /**
     * Get merchant lotteries
     */
    public function getLotteries(Request $request)
    {
        $user = auth('sanctum')->user();
        
        $query = Lottery::with(['product.category', 'tickets'])
            ->whereHas('product', function ($productQuery) use ($user) {
                $productQuery->where('merchant_id', $user->id);
            });

        // Filtres de base
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Recherche par nom ou description du produit
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($productQuery) use ($search) {
                $productQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('description', 'LIKE', "%{$search}%");
            })->orWhere('lottery_number', 'LIKE', "%{$search}%");
        }

        // Tri des résultats
        $sortBy = $request->get('sort_by', 'draw_date_asc');
        switch ($sortBy) {
            case 'end_date_asc':
            case 'draw_date_asc':
                $query->orderBy('draw_date', 'asc');
                break;
            case 'end_date_desc':
            case 'draw_date_desc':
                $query->orderBy('draw_date', 'desc');
                break;
            case 'ticket_price_asc':
                $query->orderBy('ticket_price', 'asc');
                break;
            case 'ticket_price_desc':
                $query->orderBy('ticket_price', 'desc');
                break;
            case 'popularity':
                $query->withCount('tickets')
                      ->orderBy('tickets_count', 'desc');
                break;
            default:
                $query->orderBy('draw_date', 'asc');
        }

        $perPage = min($request->get('per_page', 15), 50);
        $lotteries = $query->paginate($perPage);

        // Ajouter des informations utiles
        $lotteries->getCollection()->transform(function ($lottery) {
            $lottery->append(['time_remaining', 'participation_rate', 'is_ending_soon', 'can_draw']);
            return $lottery;
        });

        // Calculer les statistiques
        $baseStatsQuery = Lottery::whereHas('product', function ($productQuery) use ($user) {
            $productQuery->where('merchant_id', $user->id);
        });

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'active' => (clone $baseStatsQuery)->where('status', 'active')->count(),
            'pending' => (clone $baseStatsQuery)->where('status', 'pending')->count(),
            'completed' => (clone $baseStatsQuery)->whereIn('status', ['completed', 'paid'])->count(),
            'cancelled' => (clone $baseStatsQuery)->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $lotteries,
            'stats' => $stats
        ]);
    }

    /**
     * Helper method to get lotteries list (used by getLotteryPerformance when pagination is requested)
     */
    private function getLotteriesForList(Request $request)
    {
        $user = auth('sanctum')->user();
        
        $query = Lottery::with(['product.category', 'tickets'])
            ->whereHas('product', function ($productQuery) use ($user) {
                $productQuery->where('merchant_id', $user->id);
            });

        // Filtres de base
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Recherche par nom ou description du produit
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($productQuery) use ($search) {
                $productQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('description', 'LIKE', "%{$search}%");
            })->orWhere('lottery_number', 'LIKE', "%{$search}%");
        }

        // Tri des résultats
        $sortBy = $request->get('sort_by', 'draw_date_asc');
        switch ($sortBy) {
            case 'end_date_asc':
            case 'draw_date_asc':
                $query->orderBy('draw_date', 'asc');
                break;
            case 'end_date_desc':
            case 'draw_date_desc':
                $query->orderBy('draw_date', 'desc');
                break;
            case 'ticket_price_asc':
                $query->orderBy('ticket_price', 'asc');
                break;
            case 'ticket_price_desc':
                $query->orderBy('ticket_price', 'desc');
                break;
            case 'popularity':
                $query->withCount('tickets')
                      ->orderBy('tickets_count', 'desc');
                break;
            default:
                $query->orderBy('draw_date', 'asc');
        }

        $perPage = min($request->get('per_page', 15), 50);
        $lotteries = $query->paginate($perPage);

        // Transformer les données avec les champs nécessaires
        $lotteries->getCollection()->transform(function ($lottery) {
            // Calculer les valeurs
            $completionRate = $lottery->max_tickets > 0
                ? round(($lottery->sold_tickets / $lottery->max_tickets) * 100, 2)
                : 0;
            $totalRevenue = $lottery->sold_tickets * $lottery->ticket_price;
            $daysRemaining = max(0, now()->diffInDays($lottery->draw_date, false));

            return [
                'id' => $lottery->id,
                'lottery_number' => $lottery->lottery_number,
                'title' => $lottery->title,
                'status' => $lottery->status,
                'ticket_price' => floatval($lottery->ticket_price),
                'max_tickets' => intval($lottery->max_tickets),
                'total_tickets' => intval($lottery->max_tickets), // Alias pour frontend
                'sold_tickets' => intval($lottery->sold_tickets),
                'completion_rate' => $completionRate,
                'total_revenue' => $totalRevenue,
                'draw_date' => $lottery->draw_date,
                'end_date' => $lottery->draw_date, // Alias pour compatibilité
                'days_remaining' => intval($daysRemaining),
                'is_ending_soon' => $lottery->is_ending_soon ?? false,
                'can_draw' => $lottery->can_draw ?? false,
                'participation_rate' => $completionRate, // Alias
                'time_remaining' => $lottery->time_remaining,
                'product' => $lottery->product ? [
                    'id' => $lottery->product->id,
                    'name' => $lottery->product->name,
                    'image' => $lottery->product->image,
                    'image_url' => $lottery->product->image_url,
                ] : null,
            ];
        });

        // Calculer les statistiques
        $baseStatsQuery = Lottery::whereHas('product', function ($productQuery) use ($user) {
            $productQuery->where('merchant_id', $user->id);
        });

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'active' => (clone $baseStatsQuery)->where('status', 'active')->count(),
            'pending' => (clone $baseStatsQuery)->where('status', 'pending')->count(),
            'completed' => (clone $baseStatsQuery)->whereIn('status', ['completed', 'paid'])->count(),
            'cancelled' => (clone $baseStatsQuery)->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'lotteries' => $lotteries->items(), // Données transformées
            'pagination' => [
                'current_page' => $lotteries->currentPage(),
                'last_page' => $lotteries->lastPage(),
                'per_page' => $lotteries->perPage(),
                'total' => $lotteries->total(),
                'from' => $lotteries->firstItem(),
                'to' => $lotteries->lastItem(),
            ],
            'stats' => $stats
        ]);
    }
}