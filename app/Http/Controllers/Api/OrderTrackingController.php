<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\LotteryTicket;
use App\Enums\OrderStatus;
use App\Services\PaymentStatusMapper;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderTrackingController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    /**
     * Récupérer toutes les commandes de l'utilisateur connecté
     * 
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Récupérer les commandes de l'utilisateur avec pagination et filtres",
     *     description="Récupère la liste paginée des commandes de l'utilisateur connecté avec possibilité de filtrer par statut et type",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page (max 50)",
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=50),
     *         example=10
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut de la commande",
     *         @OA\Schema(type="string", enum={"pending", "awaiting_payment", "paid", "failed", "cancelled", "fulfilled", "refunded"}),
     *         example="paid"
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de commande",
     *         @OA\Schema(type="string", enum={"lottery", "direct"}),
     *         example="lottery"
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
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                     @OA\Property(property="paid_at", type="string", format="date-time", example="2024-01-15T10:35:00Z"),
     *                     @OA\Property(property="fulfilled_at", type="string", format="date-time", nullable=true, example=null),
     *                     @OA\Property(property="product", type="object", nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Loterie Jackpot"),
     *                         @OA\Property(property="image", type="string", example="lottery-image.jpg")
     *                     ),
     *                     @OA\Property(property="lottery", type="object", nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="lottery_number", type="string", example="LOT-001"),
     *                         @OA\Property(property="title", type="string", example="Loterie du Nouvel An"),
     *                         @OA\Property(property="draw_date", type="string", format="date-time", example="2024-01-31T20:00:00Z")
     *                     ),
     *                     @OA\Property(property="payments_count", type="integer", example=1),
     *                     @OA\Property(property="latest_payment_status", type="string", example="paid")
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=25)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = min($request->get('per_page', 10), 50); // Limit to 50 per page
        $status = $request->get('status');
        $type = $request->get('type');

        $query = Order::where('user_id', $user->id)
            ->with(['product', 'lottery', 'payments'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status && in_array($status, OrderStatus::values())) {
            $query->where('status', $status);
        }

        // Filter by type
        if ($type && in_array($type, ['lottery', 'direct'])) {
            $query->where('type', $type);
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->getCollection()->map(function ($order) {
                return $this->formatOrderSummary($order);
            }),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total()
            ]
        ]);
    }

    /**
     * Récupérer les détails d'une commande spécifique
     * 
     * @OA\Get(
     *     path="/api/orders/{order_number}",
     *     tags={"Orders"},
     *     summary="Récupérer les détails complets d'une commande",
     *     description="Récupère les détails complets d'une commande spécifique incluant les billets, paiements et informations produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order_number",
     *         in="path",
     *         description="Numéro unique de la commande",
     *         required=true,
     *         @OA\Schema(type="string", example="ORD-67890ABCDE")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la commande récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_number", type="string", example="ORD-67890ABCDE"),
     *                 @OA\Property(property="type", type="string", example="lottery"),
     *                 @OA\Property(property="status", type="string", example="paid"),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=5000),
     *                 @OA\Property(property="currency", type="string", example="XAF"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                 @OA\Property(property="paid_at", type="string", format="date-time", example="2024-01-15T10:35:00Z"),
     *                 @OA\Property(property="fulfilled_at", type="string", format="date-time", nullable=true, example=null),
     *                 @OA\Property(property="cancelled_at", type="string", format="date-time", nullable=true, example=null),
     *                 @OA\Property(property="refunded_at", type="string", format="date-time", nullable=true, example=null),
     *                 @OA\Property(property="notes", type="string", nullable=true, example=null),
     *                 @OA\Property(property="product", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Loterie Jackpot"),
     *                     @OA\Property(property="description", type="string", example="Grande loterie avec gros gains"),
     *                     @OA\Property(property="image", type="string", example="lottery-image.jpg"),
     *                     @OA\Property(property="sale_mode", type="string", example="lottery"),
     *                     @OA\Property(property="price", type="number", format="float", example=1000)
     *                 ),
     *                 @OA\Property(property="lottery", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="lottery_number", type="string", example="LOT-001"),
     *                     @OA\Property(property="title", type="string", example="Loterie du Nouvel An"),
     *                     @OA\Property(property="description", type="string", example="Tirage spécial Nouvel An"),
     *                     @OA\Property(property="ticket_price", type="number", format="float", example=1000),
     *                     @OA\Property(property="max_tickets", type="integer", example=1000),
     *                     @OA\Property(property="sold_tickets", type="integer", example=850),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="draw_date", type="string", format="date-time", example="2024-01-31T20:00:00Z"),
     *                     @OA\Property(property="winner_ticket_number", type="string", nullable=true, example=null)
     *                 ),
     *                 @OA\Property(property="tickets", type="array", nullable=true,
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="ticket_number", type="string", example="TICKET-123456"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="is_winner", type="boolean", example=false),
     *                         @OA\Property(property="price_paid", type="number", format="float", example=1000),
     *                         @OA\Property(property="purchased_at", type="string", format="date-time", example="2024-01-15T10:30:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="payments", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="reference", type="string", example="KMB-PAY-20240115103000-0001"),
     *                         @OA\Property(property="ebilling_id", type="string", example="EB-123456789"),
     *                         @OA\Property(property="amount", type="number", format="float", example=5000),
     *                         @OA\Property(property="status", type="string", example="paid"),
     *                         @OA\Property(property="payment_method", type="string", example="airtel_money"),
     *                         @OA\Property(property="transaction_id", type="string", example="AM-789123456"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                         @OA\Property(property="paid_at", type="string", format="date-time", example="2024-01-15T10:35:00Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commande non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Commande non trouvée")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $user = Auth::user();
        
        $order = Order::where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->with([
                'product',
                'lottery.product',
                'lottery.lotteryTickets' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
                'payments'
            ])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatOrderDetails($order)
        ]);
    }

    /**
     * Générer et télécharger la facture PDF d'une commande
     * 
     * @OA\Get(
     *     path="/api/orders/{order_number}/invoice",
     *     tags={"Orders"},
     *     summary="Générer et télécharger la facture PDF d'une commande payée",
     *     description="Génère et retourne un fichier PDF de facture pour une commande payée. La facture inclut le numéro de commande, date, informations client, lignes de commande et total en FCFA",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order_number",
     *         in="path",
     *         description="Numéro unique de la commande",
     *         required=true,
     *         @OA\Schema(type="string", example="ORD-67890ABCDE")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facture PDF générée et téléchargée avec succès",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary",
     *                 description="Fichier PDF de la facture"
     *             )
     *         ),
     *         @OA\Header(
     *             header="Content-Disposition",
     *             description="Nom du fichier PDF",
     *             @OA\Schema(type="string", example="attachment; filename=facture-ORD-67890ABCDE.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Commande non payée ou statut invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="La facture n'est disponible que pour les commandes payées")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commande non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Commande non trouvée")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de la génération de la facture",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la génération de la facture"),
     *             @OA\Property(property="error", type="string", nullable=true, example="PDF generation failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function invoice(Request $request, string $orderNumber)
    {
        $user = Auth::user();
        
        $order = Order::where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        try {
            return $this->invoiceService->make($order);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération de la facture',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Récupérer les statistiques des commandes
     * 
     * @OA\Get(
     *     path="/api/orders/stats",
     *     tags={"Orders"},
     *     summary="Récupérer les statistiques des commandes de l'utilisateur",
     *     description="Récupère les statistiques détaillées des commandes de l'utilisateur connecté incluant les totaux, montants et billets",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques des commandes récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_orders", type="integer", example=15, description="Nombre total de commandes"),
     *                 @OA\Property(property="paid_orders", type="integer", example=12, description="Nombre de commandes payées"),
     *                 @OA\Property(property="pending_orders", type="integer", example=2, description="Nombre de commandes en attente"),
     *                 @OA\Property(property="failed_orders", type="integer", example=1, description="Nombre de commandes échouées"),
     *                 @OA\Property(property="fulfilled_orders", type="integer", example=8, description="Nombre de commandes livrées"),
     *                 @OA\Property(property="total_amount_spent", type="number", format="float", example=75000, description="Montant total dépensé en FCFA"),
     *                 @OA\Property(property="lottery_orders", type="integer", example=10, description="Nombre de commandes loterie"),
     *                 @OA\Property(property="direct_orders", type="integer", example=5, description="Nombre de commandes directes"),
     *                 @OA\Property(property="total_tickets_purchased", type="integer", example=25, description="Nombre total de billets achetés"),
     *                 @OA\Property(property="winning_tickets", type="integer", example=3, description="Nombre de billets gagnants")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'paid_orders' => Order::where('user_id', $user->id)->paid()->count(),
            'pending_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', [OrderStatus::PENDING->value, OrderStatus::AWAITING_PAYMENT->value])
                ->count(),
            'failed_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', [OrderStatus::FAILED->value, OrderStatus::CANCELLED->value])
                ->count(),
            'fulfilled_orders' => Order::where('user_id', $user->id)
                ->where('status', OrderStatus::FULFILLED->value)
                ->count(),
            'total_amount_spent' => Order::where('user_id', $user->id)
                ->whereIn('status', [OrderStatus::PAID->value, OrderStatus::FULFILLED->value])
                ->sum('total_amount'),
            'lottery_orders' => Order::where('user_id', $user->id)
                ->where('type', 'lottery')
                ->count(),
            'direct_orders' => Order::where('user_id', $user->id)
                ->where('type', 'direct')
                ->count(),
            'total_tickets_purchased' => LotteryTicket::where('user_id', $user->id)
                ->whereHas('order', function($query) {
                    $query->whereIn('status', [OrderStatus::PAID->value, OrderStatus::FULFILLED->value]);
                })
                ->count(),
            'winning_tickets' => LotteryTicket::where('user_id', $user->id)
                ->where('is_winner', true)
                ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Rechercher des commandes par numéro de commande, référence ou numéro de ticket
     * 
     * @OA\Get(
     *     path="/api/orders/search",
     *     tags={"Orders"},
     *     summary="Rechercher des commandes, paiements et billets",
     *     description="Recherche dans les commandes par numéro de commande, références de paiement, et numéros de billets de loterie",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Terme de recherche (numéro de commande, référence de paiement, numéro de billet)",
     *         required=true,
     *         @OA\Schema(type="string", example="ORD-67890")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Résultats de recherche",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="orders", type="array", nullable=true,
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="order_number", type="string", example="ORD-67890ABCDE"),
     *                         @OA\Property(property="type", type="string", example="lottery"),
     *                         @OA\Property(property="status", type="string", example="paid"),
     *                         @OA\Property(property="total_amount", type="number", format="float", example=5000),
     *                         @OA\Property(property="created_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(property="payments", type="array", nullable=true,
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="reference", type="string", example="KMB-PAY-20240115103000-0001"),
     *                         @OA\Property(property="amount", type="number", format="float", example=5000),
     *                         @OA\Property(property="status", type="string", example="paid"),
     *                         @OA\Property(property="payment_method", type="string", example="airtel_money"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="order", type="object", nullable=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="tickets", type="array", nullable=true,
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="ticket_number", type="string", example="TICKET-123456"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="is_winner", type="boolean", example=false),
     *                         @OA\Property(property="price_paid", type="number", format="float", example=1000),
     *                         @OA\Property(property="purchased_at", type="string", format="date-time"),
     *                         @OA\Property(property="lottery", type="object", nullable=true),
     *                         @OA\Property(property="order", type="object", nullable=true)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Terme de recherche manquant",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Terme de recherche requis")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $search = $request->get('q');

        if (empty($search)) {
            return response()->json([
                'success' => false,
                'message' => 'Terme de recherche requis'
            ], 400);
        }

        $results = [];

        // Recherche par numéro de commande
        $orders = Order::where('user_id', $user->id)
            ->where('order_number', 'like', "%{$search}%")
            ->with(['product', 'lottery', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->count() > 0) {
            $results['orders'] = $orders->map(function ($order) {
                return $this->formatOrderSummary($order);
            });
        }

        // Recherche par référence de paiement
        $payments = Payment::where('user_id', $user->id)
            ->where(function ($query) use ($search) {
                $query->where('reference', 'like', "%{$search}%")
                    ->orWhere('ebilling_id', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            })
            ->with(['order.product', 'order.lottery'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($payments->count() > 0) {
            $results['payments'] = $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'reference' => $payment->reference,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'payment_method' => $payment->payment_method,
                    'created_at' => $payment->created_at,
                    'order' => $payment->order ? $this->formatOrderSummary($payment->order) : null
                ];
            });
        }

        // Recherche par numéro de ticket
        $tickets = LotteryTicket::where('user_id', $user->id)
            ->where('ticket_number', 'like', "%{$search}%")
            ->with(['lottery.product', 'order'])
            ->get();

        if ($tickets->count() > 0) {
            $results['tickets'] = $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                    'is_winner' => $ticket->is_winner,
                    'price_paid' => $ticket->price,
                    'purchased_at' => $ticket->purchased_at,
                    'lottery' => $ticket->lottery ? [
                        'id' => $ticket->lottery->id,
                        'lottery_number' => $ticket->lottery->lottery_number,
                        'title' => $ticket->lottery->title
                    ] : null,
                    'order' => $ticket->order ? 
                        $this->formatOrderSummary($ticket->order) : null
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Format order data for summary view (list)
     */
    private function formatOrderSummary(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'type' => $order->type,
            'status' => $order->status,
            'total_amount' => $order->total_amount,
            'currency' => $order->currency,
            'created_at' => $order->created_at,
            'paid_at' => $order->paid_at,
            'fulfilled_at' => $order->fulfilled_at,
            'product' => $order->product ? [
                'id' => $order->product->id,
                'name' => $order->product->name,
                'image' => $order->product->image,
            ] : null,
            'lottery' => $order->lottery ? [
                'id' => $order->lottery->id,
                'lottery_number' => $order->lottery->lottery_number,
                'title' => $order->lottery->title,
                'draw_date' => $order->lottery->draw_date,
            ] : null,
            'payments_count' => $order->payments ? $order->payments->count() : 0,
            'latest_payment_status' => $order->payments && $order->payments->count() > 0 ? 
                $order->payments->sortByDesc('created_at')->first()->status : null
        ];
    }

    /**
     * Format order data for detailed view (single order)
     */
    private function formatOrderDetails(Order $order): array
    {
        $details = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'type' => $order->type,
            'status' => $order->status,
            'total_amount' => $order->total_amount,
            'currency' => $order->currency,
            'created_at' => $order->created_at,
            'paid_at' => $order->paid_at,
            'fulfilled_at' => $order->fulfilled_at,
            'cancelled_at' => $order->cancelled_at,
            'refunded_at' => $order->refunded_at,
            'notes' => $order->notes,
        ];

        // Product information
        if ($order->product) {
            $details['product'] = [
                'id' => $order->product->id,
                'name' => $order->product->name,
                'description' => $order->product->description,
                'image' => $order->product->image,
                'sale_mode' => $order->product->sale_mode,
                'price' => $order->product->price,
            ];
        }

        // Lottery information
        if ($order->lottery) {
            $details['lottery'] = [
                'id' => $order->lottery->id,
                'lottery_number' => $order->lottery->lottery_number,
                'title' => $order->lottery->title,
                'description' => $order->lottery->description,
                'ticket_price' => $order->lottery->ticket_price,
                'max_tickets' => $order->lottery->max_tickets,
                'sold_tickets' => $order->lottery->sold_tickets,
                'status' => $order->lottery->status,
                'draw_date' => $order->lottery->draw_date,
                'winner_ticket_number' => $order->lottery->winner_ticket_number,
            ];

            // Lottery tickets for this order
            if ($order->lottery->tickets && $order->lottery->tickets->count() > 0) {
                $details['tickets'] = $order->lottery->tickets->map(function ($ticket) {
                    return [
                        'id' => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'status' => $ticket->status,
                        'is_winner' => $ticket->is_winner,
                        'price_paid' => $ticket->price,
                        'purchased_at' => $ticket->purchased_at,
                    ];
                });
            }
        }

        // Payment information
        if ($order->payments && $order->payments->count() > 0) {
            $details['payments'] = $order->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'reference' => $payment->reference,
                    'ebilling_id' => $payment->ebilling_id,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->transaction_id,
                    'created_at' => $payment->created_at,
                    'paid_at' => $payment->paid_at,
                ];
            });
        }

        return $details;
    }
}