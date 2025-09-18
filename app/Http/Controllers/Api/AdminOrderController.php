<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Product;
use App\Models\Lottery;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Admin Orders",
 *     description="API endpoints pour la gestion des commandes par l'admin"
 * )
 */
class AdminOrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/orders",
     *     tags={"Admin Orders"},
     *     summary="Liste paginée des commandes pour l'admin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type (lottery|direct)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"lottery", "direct"})
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filtrer par utilisateur",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Date de début (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Date de fin (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de commande, email utilisateur, nom produit",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des commandes récupérée avec succès"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 100);
        
        $query = Order::with([
            'user:id,first_name,last_name,email,phone',
            'product:id,name,price,image',
            'lottery:id,title,ticket_price,max_tickets,sold_tickets,draw_date,status',
            'payments'
        ])->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate($perPage);

        // Enrichir les données
        $ordersData = $orders->items();
        foreach ($ordersData as $order) {
            // Ajouter les informations calculées
            $order->customer_name = trim(($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? ''));
            $order->status_label = $this->getStatusLabel($order->status);
            $order->type_label = $this->getTypeLabel($order->type);
            
            // Informations de paiement
            if ($order->payments->count() > 0) {
                $latestPayment = $order->payments->sortByDesc('created_at')->first();
                $order->payment_status = $latestPayment->status;
                $order->payment_method = $latestPayment->payment_method;
                $order->payment_reference = $latestPayment->reference;
            }

            // Informations produit/loterie
            if ($order->lottery) {
                $order->item_name = $order->lottery->title;
                $order->item_price = $order->lottery->ticket_price;
                $order->lottery_progress = $order->lottery->max_tickets > 0 ? 
                    round(($order->lottery->sold_tickets / $order->lottery->max_tickets) * 100) : 0;
            } elseif ($order->product) {
                $order->item_name = $order->product->name;
                $order->item_price = $order->product->price;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $ordersData,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/orders/stats",
     *     tags={"Admin Orders"},
     *     summary="Statistiques des commandes pour l'admin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Statistiques récupérées avec succès")
     * )
     */
    public function stats(): JsonResponse
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $stats = [
            // Statistiques globales
            'total_orders' => Order::count(),
            'total_amount' => Order::whereIn('status', ['paid', 'fulfilled'])
                ->where('type', 'lottery')
                ->join('lotteries', 'orders.lottery_id', '=', 'lotteries.id')
                ->join('products', 'lotteries.product_id', '=', 'products.id')
                ->selectRaw('SUM(orders.total_amount - products.price) as koumbaya_margin')
                ->value('koumbaya_margin') ?: 0,
            
            // Par statut
            'pending_orders' => Order::where('status', OrderStatus::PENDING->value)->count(),
            'paid_orders' => Order::where('status', OrderStatus::PAID->value)->count(),
            'fulfilled_orders' => Order::where('status', OrderStatus::FULFILLED->value)->count(),
            'cancelled_orders' => Order::where('status', OrderStatus::CANCELLED->value)->count(),
            
            // Par type
            'lottery_orders' => Order::where('type', 'lottery')->count(),
            'direct_orders' => Order::where('type', 'direct')->count(),
            
            // Temporelles
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'today_amount' => Order::whereDate('created_at', $today)->sum('total_amount'),
            
            'this_month_orders' => Order::where('created_at', '>=', $thisMonth)->count(),
            'this_month_amount' => Order::where('created_at', '>=', $thisMonth)->sum('total_amount'),
            
            'last_month_orders' => Order::whereBetween('created_at', [
                $lastMonth, 
                Carbon::now()->subMonth()->endOfMonth()
            ])->count(),
            
            // Moyennes
            'average_order_value' => round(Order::avg('total_amount'), 2),
            
            // Top utilisateurs (par nombre de commandes)
            'top_customers' => User::withCount('orders')
                ->with('orders:user_id,total_amount')
                ->having('orders_count', '>', 0)
                ->orderBy('orders_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                        'email' => $user->email,
                        'orders_count' => $user->orders_count,
                        'total_spent' => $user->orders->sum('total_amount')
                    ];
                }),
                
            // Évolution mensuelle (6 derniers mois)
            'monthly_evolution' => $this->getMonthlyEvolution(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/orders/{orderNumber}",
     *     tags={"Admin Orders"},
     *     summary="Détails d'une commande pour l'admin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orderNumber",
     *         in="path",
     *         description="Numéro de la commande",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Détails de la commande récupérés avec succès")
     * )
     */
    public function show(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with([
                'user',
                'product',
                'lottery.product',
                'lottery.tickets.user:id,first_name,last_name,email',
                'payments'
            ])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        // Enrichir les données
        $order->customer_name = $order->user 
            ? trim(($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? ''))
            : 'Client non disponible';
        $order->status_label = $this->getStatusLabel($order->status);
        $order->type_label = $this->getTypeLabel($order->type);

        // Timeline de la commande
        $timeline = [
            [
                'type' => 'created',
                'title' => 'Commande créée',
                'description' => "Commande #{$order->order_number} créée",
                'timestamp' => $order->created_at,
                'status' => 'completed'
            ]
        ];

        if ($order->paid_at) {
            $timeline[] = [
                'type' => 'paid',
                'title' => 'Paiement effectué',
                'description' => 'Paiement confirmé et validé',
                'timestamp' => $order->paid_at,
                'status' => 'completed'
            ];
        }

        if ($order->fulfilled_at) {
            $timeline[] = [
                'type' => 'fulfilled',
                'title' => 'Commande traitée',
                'description' => 'Commande traitée et livrée',
                'timestamp' => $order->fulfilled_at,
                'status' => 'completed'
            ];
        }

        $order->timeline = collect($timeline)->sortBy('timestamp')->values();

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/orders/{orderNumber}/status",
     *     tags={"Admin Orders"},
     *     summary="Mettre à jour le statut d'une commande",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orderNumber",
     *         in="path",
     *         description="Numéro de la commande",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "paid", "fulfilled", "cancelled"}),
     *             @OA\Property(property="reason", type="string", description="Raison du changement (optionnel)")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Statut mis à jour avec succès")
     * )
     */
    public function updateStatus(Request $request, string $orderNumber): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,paid,fulfilled,cancelled',
            'reason' => 'nullable|string|max:500'
        ]);

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Le statut est déjà défini sur cette valeur'
            ], 400);
        }

        try {
            DB::transaction(function () use ($order, $newStatus, $request) {
                $order->status = $newStatus;
                
                // Mettre à jour les timestamps selon le statut
                switch ($newStatus) {
                    case 'paid':
                        if (!$order->paid_at) {
                            $order->paid_at = now();
                        }
                        break;
                    case 'fulfilled':
                        if (!$order->fulfilled_at) {
                            $order->fulfilled_at = now();
                        }
                        break;
                }

                $order->save();

                // Log l'action
                Log::info("Admin updated order status", [
                    'order_number' => $order->order_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'reason' => $request->reason,
                    'admin_user' => auth()->user()->email
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Statut de la commande mis à jour avec succès',
                'data' => [
                    'order_number' => $order->order_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'status_label' => $this->getStatusLabel($newStatus)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating order status', [
                'error' => $e->getMessage(),
                'order_number' => $orderNumber
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut'
            ], 500);
        }
    }

    /**
     * Obtenir l'évolution mensuelle des commandes
     */
    private function getMonthlyEvolution(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            $orderCount = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $totalAmount = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount');
            
            $months[] = [
                'month' => $date->format('M Y'),
                'month_short' => $date->format('M'),
                'orders' => $orderCount,
                'amount' => $totalAmount
            ];
        }
        
        return $months;
    }

    /**
     * Obtenir le libellé du statut
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'paid' => 'Payée',
            'fulfilled' => 'Traitée',
            'cancelled' => 'Annulée',
            'refunded' => 'Remboursée',
            default => ucfirst($status)
        };
    }

    /**
     * Obtenir le libellé du type
     */
    private function getTypeLabel(string $type): string
    {
        return match($type) {
            'lottery' => 'Tombola',
            'direct' => 'Achat direct',
            default => ucfirst($type)
        };
    }
}