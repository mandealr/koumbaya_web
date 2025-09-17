<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Enums\OrderStatus;
use App\Services\MetricsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Merchant Orders",
 *     description="API endpoints pour la gestion des commandes marchands avec export CSV"
 * )
 */
class MerchantOrderController extends Controller
{
    protected MetricsService $metricsService;

    public function __construct(MetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
        $this->middleware(['auth:sanctum', 'merchant']);
    }

    /**
     * Récupérer les commandes du marchand avec filtres et pagination
     * 
     * @OA\Get(
     *     path="/api/merchant/orders",
     *     tags={"Merchant Orders"},
     *     summary="Récupérer les commandes du marchand avec filtres et pagination",
     *     description="Récupère la liste paginée des commandes du marchand connecté avec possibilité de filtrer par dates, statut, type et produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page (max 100)",
     *         @OA\Schema(type="integer", default=15, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Date de début (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Date de fin (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut de la commande",
     *         @OA\Schema(type="string", enum={"pending", "awaiting_payment", "paid", "failed", "cancelled", "fulfilled", "refunded"})
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de commande",
     *         @OA\Schema(type="string", enum={"lottery", "direct"})
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Filtrer par ID de produit",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de commande, nom client ou email",
     *         @OA\Schema(type="string", example="ORD-123")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Trier par",
     *         @OA\Schema(type="string", enum={"created_at_desc", "created_at_asc", "amount_desc", "amount_asc", "status"}, default="created_at_desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des commandes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="order_number", type="string", example="ORD-67890ABCDE"),
     *                     @OA\Property(property="type", type="string", example="lottery"),
     *                     @OA\Property(property="status", type="string", example="paid"),
     *                     @OA\Property(property="total_amount", type="number", format="float", example=5000),
     *                     @OA\Property(property="currency", type="string", example="XAF"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="customer", type="object",
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="phone", type="string", example="06123456")
     *                     ),
     *                     @OA\Property(property="product", type="object", nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Loterie Jackpot"),
     *                         @OA\Property(property="image", type="string", example="product.jpg")
     *                     ),
     *                     @OA\Property(property="payments_count", type="integer", example=1),
     *                     @OA\Property(property="tickets_count", type="integer", example=5)
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *             ),
     *             @OA\Property(property="summary", type="object",
     *                 @OA\Property(property="total_orders", type="integer", example=150),
     *                 @OA\Property(property="total_revenue", type="number", format="float", example=750000),
     *                 @OA\Property(property="avg_order_value", type="number", format="float", example=5000),
     *                 @OA\Property(property="status_breakdown", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié ou pas de rôle marchand",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $merchantId = Auth::id();
        $perPage = min($request->get('per_page', 15), 100);

        // Base query - récupérer les commandes des produits du marchand
        // Support for both direct sales and lottery orders
        $query = Order::with([
            'user:id,first_name,last_name,email,phone',
            'product:id,name,image,merchant_id,price',
            'lottery:id,lottery_number,title,product_id,ticket_price',
            'lottery.product:id,name,image,merchant_id,price',
            'payments:id,order_id,amount,status,payment_method',
            'tickets:id,ticket_number,is_winner'
        ])
        ->where(function ($q) use ($merchantId) {
            // Direct product orders
            $q->whereHas('product', function ($subQ) use ($merchantId) {
                $subQ->where('merchant_id', $merchantId);
            })
            // OR lottery orders
            ->orWhere(function ($subQ) use ($merchantId) {
                $subQ->where('type', 'lottery')
                    ->whereHas('lottery.product', function ($lotteryQ) use ($merchantId) {
                        $lotteryQ->where('merchant_id', $merchantId);
                    });
            });
        });

        // Filtres par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filtre par statut
        if ($request->filled('status') && in_array($request->status, OrderStatus::values())) {
            $query->where('status', $request->status);
        }

        // Filtre par type
        if ($request->filled('type') && in_array($request->type, ['lottery', 'direct'])) {
            $query->where('type', $request->type);
        }

        // Filtre par produit
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at_desc');
        switch ($sortBy) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'amount_desc':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'amount_asc':
                $query->orderBy('total_amount', 'asc');
                break;
            case 'status':
                $query->orderBy('status', 'asc')->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $orders = $query->paginate($perPage);

        // Calculer les résumés et statistiques
        $summary = $this->calculateOrdersSummary($merchantId, $request);

        return response()->json([
            'success' => true,
            'data' => collect($orders->items())->map(function ($order) {
                return $this->formatOrderForMerchant($order);
            }),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total()
            ],
            'summary' => $summary
        ]);
    }

    /**
     * Exporter les commandes au format CSV
     * 
     * @OA\Get(
     *     path="/api/merchant/orders/export",
     *     tags={"Merchant Orders"},
     *     summary="Exporter les commandes au format CSV",
     *     description="Génère et télécharge un fichier CSV contenant les commandes du marchand selon les filtres appliqués",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Date de début pour l'export",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Date de fin pour l'export", 
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Filtrer par produit",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fichier CSV généré avec succès",
     *         @OA\MediaType(
     *             mediaType="text/csv",
     *             @OA\Schema(type="string", format="binary")
     *         ),
     *         @OA\Header(
     *             header="Content-Disposition",
     *             description="Nom du fichier CSV",
     *             @OA\Schema(type="string")
     *         )
     *     )
     * )
     */
    public function exportCsv(Request $request): Response
    {
        $merchantId = Auth::id();
        
        // Même logique de filtrage que l'index mais sans pagination
        $query = Order::with([
            'user:id,first_name,last_name,email,phone',
            'product:id,name,merchant_id',
            'lottery:id,lottery_number,title',
            'payments',
            'tickets'
        ])
        ->whereHas('product', function ($q) use ($merchantId) {
            $q->where('merchant_id', $merchantId);
        });

        // Appliquer les mêmes filtres
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Limite de sécurité pour éviter les exports trop volumineux
        $orders = $query->orderBy('created_at', 'desc')->limit(10000)->get();

        // Générer le CSV
        $csv = $this->generateCsv($orders);
        
        $filename = 'commandes_' . date('Y-m-d_H-i-s') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Récupérer les produits top du marchand (réutilise la logique existante)
     * 
     * @OA\Get(
     *     path="/api/merchant/orders/top-products",
     *     tags={"Merchant Orders"},
     *     summary="Récupérer les produits les plus performants pour le filtre",
     *     description="Retourne la liste des produits du marchand triés par performance pour alimenter les filtres de sélection",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de produits à retourner",
     *         @OA\Schema(type="integer", default=20, maximum=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des produits performance",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="total_orders", type="integer"),
     *                     @OA\Property(property="total_revenue", type="number"),
     *                     @OA\Property(property="avg_order_value", type="number")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getTopProducts(Request $request): JsonResponse
    {
        $merchantId = Auth::id();
        $limit = min($request->get('limit', 20), 50);

        // Réutiliser la logique de sélection des top products du dashboard
        $topProducts = Product::select([
            'products.*',
            DB::raw('COALESCE(COUNT(orders.id), 0) as total_orders'),
            DB::raw('COALESCE(SUM(orders.total_amount), 0) as total_revenue'),
            DB::raw('COALESCE(AVG(orders.total_amount), 0) as avg_order_value')
        ])
        ->leftJoin('orders', function ($join) {
            $join->on('products.id', '=', 'orders.product_id')
                 ->whereIn('orders.status', [OrderStatus::PAID->value, OrderStatus::FULFILLED->value]);
        })
        ->where('products.merchant_id', $merchantId)
        ->groupBy('products.id')
        ->orderBy('total_revenue', 'desc')
        ->limit($limit)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $topProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'total_orders' => intval($product->total_orders),
                    'total_revenue' => floatval($product->total_revenue),
                    'avg_order_value' => floatval($product->avg_order_value),
                    'image' => $product->image,
                ];
            })
        ]);
    }

    /**
     * Formater une commande pour l'affichage marchand
     */
    private function formatOrderForMerchant(Order $order): array
    {
        $product = $this->getOrderProduct($order);
        $ticketPrice = 0;
        
        // Get ticket price from lottery or product
        if ($order->lottery && $order->lottery->ticket_price) {
            $ticketPrice = $order->lottery->ticket_price;
        } elseif ($order->product && $order->product->price) {
            $ticketPrice = $order->product->price;
        }
        
        // Get ticket numbers
        $ticketNumbers = [];
        if ($order->tickets) {
            $ticketNumbers = $order->tickets->pluck('ticket_number')->toArray();
        }
        
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'type' => $order->type,
            'status' => $order->status,
            'total_amount' => $order->total_amount ?? 0,
            'currency' => $order->currency ?? 'XAF',
            'created_at' => $order->created_at,
            'paid_at' => $order->paid_at,
            'fulfilled_at' => $order->fulfilled_at,
            
            // Customer data (flat structure for frontend compatibility)
            'customer_name' => $order->user ? ($order->user->first_name . ' ' . $order->user->last_name) : 'Client anonyme',
            'customer_email' => $order->user?->email ?? '',
            'customer_phone' => $order->user?->phone ?? '',
            
            // Product data (flat structure for frontend compatibility)
            'product_name' => $product ? $product['name'] : 'Produit inconnu',
            'product_image' => $product ? $product['image'] : '',
            'ticket_price' => $ticketPrice,
            
            // Ticket data
            'tickets_count' => $order->tickets ? $order->tickets->count() : 0,
            'ticket_numbers' => $ticketNumbers,
            
            // Nested structures for API completeness
            'customer' => [
                'name' => $order->user ? ($order->user->first_name . ' ' . $order->user->last_name) : 'Client anonyme',
                'email' => $order->user?->email ?? '',
                'phone' => $order->user?->phone ?? '',
            ],
            'product' => $product,
            'lottery' => $order->lottery ? [
                'id' => $order->lottery->id,
                'lottery_number' => $order->lottery->lottery_number,
                'title' => $order->lottery->title,
            ] : null,
            'payments_count' => $order->payments ? $order->payments->count() : 0,
            'latest_payment_status' => $order->payments && $order->payments->count() > 0 
                ? $order->payments->sortByDesc('created_at')->first()->status 
                : null,
            'has_winning_ticket' => $order->tickets 
                ? $order->tickets->contains('is_winner', true)
                : false,
        ];
    }

    /**
     * Get product data for an order (direct or via lottery)
     */
    private function getOrderProduct(Order $order): ?array
    {
        // Direct product order
        if ($order->product) {
            return [
                'id' => $order->product->id,
                'name' => $order->product->name,
                'image' => $order->product->image,
            ];
        }
        
        // Lottery order - get product via lottery
        if ($order->lottery && $order->lottery->product) {
            return [
                'id' => $order->lottery->product->id,
                'name' => $order->lottery->product->name,
                'image' => $order->lottery->product->image,
            ];
        }
        
        return null;
    }

    /**
     * Calculer le résumé des commandes
     */
    private function calculateOrdersSummary(int $merchantId, Request $request): array
    {
        // Base query pour les calculs de résumé - Support lottery and direct orders
        $baseQuery = Order::where(function ($q) use ($merchantId) {
            // Direct product orders
            $q->whereHas('product', function ($subQ) use ($merchantId) {
                $subQ->where('merchant_id', $merchantId);
            })
            // OR lottery orders
            ->orWhere(function ($subQ) use ($merchantId) {
                $subQ->where('type', 'lottery')
                    ->whereHas('lottery.product', function ($lotteryQ) use ($merchantId) {
                        $lotteryQ->where('merchant_id', $merchantId);
                    });
            });
        });

        // Appliquer les mêmes filtres que la requête principale
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('product_id')) {
            $baseQuery->where('product_id', $request->product_id);
        }

        // Calculs
        $totalOrders = (clone $baseQuery)->count();
        $totalRevenue = (clone $baseQuery)
            ->whereIn('status', [OrderStatus::PAID->value, OrderStatus::FULFILLED->value])
            ->sum('total_amount');
        
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Répartition par statut
        $statusBreakdown = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => [
                    'count' => intval($item->count),
                    'revenue' => floatval($item->revenue)
                ]];
            });

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => floatval($totalRevenue),
            'avg_order_value' => floatval($avgOrderValue),
            'status_breakdown' => $statusBreakdown,
            'period' => [
                'from' => $request->date_from ?? null,
                'to' => $request->date_to ?? null,
            ]
        ];
    }

    /**
     * Générer le contenu CSV
     */
    private function generateCsv($orders): string
    {
        // Headers CSV avec BOM UTF-8 pour Excel
        $output = "\xEF\xBB\xBF";
        
        // En-têtes de colonnes
        $headers = [
            'Numéro Commande',
            'Type',
            'Statut', 
            'Montant (FCFA)',
            'Devise',
            'Date Création',
            'Date Paiement',
            'Date Livraison',
            'Client Nom',
            'Client Email',
            'Client Téléphone',
            'Produit',
            'Tombola',
            'Nb Paiements',
            'Nb Billets',
            'Statut Dernier Paiement',
            'Billet Gagnant',
        ];

        $output .= '"' . implode('","', $headers) . '"' . "\n";

        // Lignes de données
        foreach ($orders as $order) {
            $row = [
                $order->order_number,
                $order->type === 'lottery' ? 'Tombola' : 'Achat Direct',
                $this->getStatusLabel($order->status),
                number_format($order->total_amount, 0, ',', ' '),
                $order->currency,
                $order->created_at ? $order->created_at->format('d/m/Y H:i') : '',
                $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : '',
                $order->fulfilled_at ? $order->fulfilled_at->format('d/m/Y H:i') : '',
                $order->user?->name ?? 'Client anonyme',
                $order->user?->email ?? '',
                $order->user?->phone ?? '',
                $order->product?->name ?? '',
                $order->lottery ? $order->lottery->lottery_number . ' - ' . $order->lottery->title : '',
                $order->payments ? $order->payments->count() : 0,
                $order->tickets ? $order->tickets->count() : 0,
                $order->payments && $order->payments->count() > 0 
                    ? $this->getPaymentStatusLabel($order->payments->sortByDesc('created_at')->first()->status)
                    : '',
                $order->tickets && $order->tickets->contains('is_winner', true) ? 'Oui' : 'Non',
            ];

            // Échapper les guillemets et entourer chaque champ de guillemets
            $escapedRow = array_map(function ($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row);

            $output .= implode(',', $escapedRow) . "\n";
        }

        return $output;
    }

    /**
     * Obtenir le libellé du statut de commande
     */
    private function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'En attente',
            'awaiting_payment' => 'En attente de paiement',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            'fulfilled' => 'Livré',
            'refunded' => 'Remboursé',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Obtenir le libellé du statut de paiement
     */
    private function getPaymentStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'expired' => 'Expiré',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Récupérer les métriques de l'application
     * 
     * @OA\Get(
     *     path="/api/merchant/orders/metrics",
     *     tags={"Merchant Orders"},
     *     summary="Récupérer les métriques applicatives",
     *     description="Récupère les compteurs applicatifs et métriques de performance",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Date de début pour les métriques (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Date de fin pour les métriques (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Métriques récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="counters", type="object",
     *                     @OA\Property(property="orders_created", type="integer", example=150),
     *                     @OA\Property(property="orders_paid", type="integer", example=120),
     *                     @OA\Property(property="payments_callback_received", type="integer", example=145),
     *                     @OA\Property(property="payments_failed", type="integer", example=25),
     *                     @OA\Property(property="invoice_generated", type="integer", example=100)
     *                 ),
     *                 @OA\Property(property="daily_metrics", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="performance_metrics", type="object"),
     *                 @OA\Property(property="conversion_metrics", type="object"),
     *                 @OA\Property(property="period", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function metrics(Request $request): JsonResponse
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : null;
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : null;

        $metrics = $this->metricsService->getMetrics($dateFrom, $dateTo);

        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Vérifier l'état de santé du système de métriques
     * 
     * @OA\Get(
     *     path="/api/merchant/orders/metrics/health",
     *     tags={"Merchant Orders"},
     *     summary="Vérifier l'état de santé du système de métriques",
     *     description="Vérifie que le système de métriques fonctionne correctement",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="État de santé du système de métriques",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="status", type="string", example="healthy"),
     *                 @OA\Property(property="cache_connectivity", type="boolean", example=true),
     *                 @OA\Property(property="counter_functionality", type="boolean", example=true),
     *                 @OA\Property(property="current_counters", type="integer", example=5),
     *                 @OA\Property(property="checked_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function metricsHealth(): JsonResponse
    {
        $healthCheck = $this->metricsService->healthCheck();

        return response()->json([
            'success' => true,
            'data' => $healthCheck
        ]);
    }
}