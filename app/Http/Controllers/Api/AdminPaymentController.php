<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    /**
     * Get paginated list of all payments with filters
     */
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'order.product', 'order.lottery.product']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'LIKE', "%{$search}%")
                  ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereIn('status', ['paid', 'processed']);
            } elseif ($request->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->status === 'failed') {
                $query->whereIn('status', ['failed', 'expired']);
            }
        }

        // Date range filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $payments = $query->paginate($perPage);

        // Transform payments for admin view
        $payments->getCollection()->transform(function ($payment) {
            return [
                'id' => $payment->id,
                'order_number' => $payment->order->order_number ?? $payment->reference,
                'reference' => $payment->reference,
                'user_id' => $payment->user_id,
                'user_name' => $payment->user ?
                    trim($payment->user->first_name . ' ' . $payment->user->last_name) : 'N/A',
                'user_email' => $payment->user->email ?? 'N/A',
                'product_name' => $payment->lottery && $payment->lottery->product
                    ? ($payment->lottery->product->title ?? $payment->lottery->product->name)
                    : ($payment->product ? ($payment->product->title ?? $payment->product->name) : 'N/A'),
                'tickets_count' => $payment->quantity ?? 1,
                'amount' => $payment->amount,
                'currency' => $payment->currency ?? 'FCFA',
                'status' => $payment->is_paid 
                    ? 'completed' 
                    : ($payment->failed_at ? 'failed' : 'pending'),
                'payment_method' => $payment->payment_method ?? 'Mobile Money',
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
                'paid_at' => $payment->paid_at,
                'failed_at' => $payment->failed_at,
                'type' => $payment->lottery ? 'lottery_ticket' : 'product_purchase'
            ];
        });

        // Get statistics
        $stats = $this->getPaymentStats();

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $payments->items(),
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ],
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Get payment statistics
     */
    public function stats()
    {
        $stats = $this->getPaymentStats();
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get a single payment with details
     */
    public function show($id)
    {
        $payment = Payment::with([
            'user',
            'order.product',
            'order.lottery.product',
            'refunds'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Initiate refund for a payment
     */
    public function refund(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if (!in_array($payment->status, ['paid', 'processed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les paiements complétés peuvent être remboursés'
            ], 422);
        }

        // Check if refund already exists
        $existingRefund = Refund::where('payment_id', $payment->id)->first();
        if ($existingRefund) {
            return response()->json([
                'success' => false,
                'message' => 'Un remboursement existe déjà pour ce paiement'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create refund record
            $refund = Refund::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'lottery_id' => $payment->lottery_id,
                'order_number' => $payment->order->order_number ?? null,
                'amount' => $payment->amount,
                'reason' => $request->reason ?? 'admin_initiated',
                'type' => 'admin',
                'status' => 'pending',
                'requested_by' => auth()->id(),
                'metadata' => [
                    'original_payment' => $payment->toArray(),
                    'initiated_by_admin' => auth()->user()->email
                ]
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Remboursement initié avec succès',
                'data' => ['refund' => $refund]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initiation du remboursement'
            ], 500);
        }
    }

    /**
     * Export payments data
     */
    public function export(Request $request)
    {
        $query = Payment::with(['user', 'order.product', 'order.lottery.product']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'LIKE', "%{$search}%")
                  ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereIn('status', ['paid', 'processed']);
            } elseif ($request->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->status === 'failed') {
                $query->whereIn('status', ['failed', 'expired']);
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $exportData = $payments->map(function ($payment) {
            return [
                'order_number' => $payment->order->order_number ?? $payment->reference,
                'reference' => $payment->reference,
                'user_name' => $payment->user ? 
                    trim($payment->user->first_name . ' ' . $payment->user->last_name) : 'N/A',
                'user_email' => $payment->user->email ?? 'N/A',
                'product_name' => $payment->lottery && $payment->lottery->product
                    ? ($payment->lottery->product->title ?? $payment->lottery->product->name)
                    : ($payment->product ? ($payment->product->title ?? $payment->product->name) : 'N/A'),
                'amount' => $payment->amount,
                'currency' => $payment->currency ?? 'FCFA',
                'status' => $payment->is_paid 
                    ? 'completed' 
                    : ($payment->failed_at ? 'failed' : 'pending'),
                'payment_method' => $payment->payment_method ?? 'Mobile Money',
                'created_at' => $payment->created_at->toDateTimeString(),
                'paid_at' => $payment->paid_at?->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $exportData,
                'export_date' => now()->toDateTimeString(),
                'total_count' => $exportData->count()
            ]
        ]);
    }

    /**
     * Calculate payment statistics
     */
    private function getPaymentStats()
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        // Current month stats
        $currentMonthRevenue = Payment::whereIn('status', ['paid', 'processed'])
            ->whereBetween('paid_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('amount');

        $currentMonthTransactions = Payment::whereIn('status', ['paid', 'processed'])
            ->whereBetween('paid_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        // Last month stats for comparison
        $lastMonthRevenue = Payment::whereIn('status', ['paid', 'processed'])
            ->whereBetween('paid_at', [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()])
            ->sum('amount');

        $lastMonthTransactions = Payment::whereIn('status', ['paid', 'processed'])
            ->whereBetween('paid_at', [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()])
            ->count();

        // Calculate changes
        $revenueChange = $lastMonthRevenue > 0 
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
            : 0;

        $transactionsChange = $lastMonthTransactions > 0 
            ? (($currentMonthTransactions - $lastMonthTransactions) / $lastMonthTransactions) * 100 
            : 0;

        // Success rate
        $totalTransactions = Payment::count();
        $successfulTransactions = Payment::whereIn('status', ['paid', 'processed'])->count();
        $successRate = $totalTransactions > 0 ? ($successfulTransactions / $totalTransactions) * 100 : 0;

        $lastMonthTotal = Payment::whereBetween('created_at', [
            $lastMonth->copy()->startOfMonth(), 
            $lastMonth->copy()->endOfMonth()
        ])->count();

        $lastMonthSuccessful = Payment::whereIn('status', ['paid', 'processed'])
            ->whereBetween('paid_at', [
                $lastMonth->copy()->startOfMonth(), 
                $lastMonth->copy()->endOfMonth()
            ])->count();

        $lastMonthSuccessRate = $lastMonthTotal > 0 ? ($lastMonthSuccessful / $lastMonthTotal) * 100 : 0;
        $successRateChange = $lastMonthSuccessRate > 0 
            ? (($successRate - $lastMonthSuccessRate) / $lastMonthSuccessRate) * 100 
            : 0;

        // Failed transactions
        $failedTransactions = Payment::whereIn('status', ['failed', 'expired'])->count();

        return [
            'total_revenue' => Payment::whereIn('status', ['paid', 'processed'])->sum('amount'),
            'current_month_revenue' => $currentMonthRevenue,
            'revenue_change' => round($revenueChange, 1),
            'successful_transactions' => $successfulTransactions,
            'current_month_transactions' => $currentMonthTransactions,
            'transactions_change' => round($transactionsChange, 1),
            'success_rate' => round($successRate, 1),
            'success_rate_change' => round($successRateChange, 1),
            'failed_transactions' => $failedTransactions,
            'pending_transactions' => Payment::where('status', 'pending')->count(),
        ];
    }
}