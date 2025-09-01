<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetricsService
{
    private const METRICS_CACHE_PREFIX = 'metrics:';
    private const COUNTER_CACHE_TTL = 3600; // 1 hour

    /**
     * Track order created event
     */
    public function orderCreated(Order $order): void
    {
        // Increment counter
        $this->incrementCounter('orders_created');
        $this->incrementCounter("orders_created_type_{$order->type}");
        
        // Log structured event
        Log::info('order.created', [
            'event' => 'orders_created',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'type' => $order->type,
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'user_id' => $order->user_id,
            'product_id' => $order->product_id,
            'lottery_id' => $order->lottery_id,
            'status' => $order->status,
            'created_at' => $order->created_at->toISOString(),
            'context' => [
                'user_agent' => request()->header('User-Agent'),
                'ip' => request()->ip(),
                'session_id' => session()->getId(),
            ]
        ]);

        // Update daily metrics
        $this->updateDailyMetric('orders_created', now(), $order->total_amount);
    }

    /**
     * Track order paid event
     */
    public function orderPaid(Order $order): void
    {
        // Increment counters
        $this->incrementCounter('orders_paid');
        $this->incrementCounter("orders_paid_type_{$order->type}");
        
        // Log structured event
        Log::info('order.paid', [
            'event' => 'orders_paid',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'type' => $order->type,
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'user_id' => $order->user_id,
            'product_id' => $order->product_id,
            'lottery_id' => $order->lottery_id,
            'paid_at' => $order->paid_at?->toISOString(),
            'payment_duration_seconds' => $order->paid_at && $order->created_at 
                ? $order->paid_at->diffInSeconds($order->created_at) 
                : null,
            'context' => [
                'previous_status' => $order->getOriginal('status'),
                'user_agent' => request()->header('User-Agent'),
                'ip' => request()->ip(),
            ]
        ]);

        // Update revenue metrics
        $this->updateDailyMetric('orders_paid', now(), $order->total_amount);
        $this->addToRevenueMetrics($order->total_amount);
    }

    /**
     * Track payment callback received event
     */
    public function paymentCallbackReceived(array $callbackData, ?Payment $payment = null): void
    {
        // Increment counters
        $this->incrementCounter('payments_callback_received');
        $this->incrementCounter("payments_callback_{$callbackData['status']}_received");
        
        if (isset($callbackData['paymentsystem'])) {
            $this->incrementCounter("payments_callback_{$callbackData['paymentsystem']}_received");
        }

        // Log structured event
        Log::info('payment.callback.received', [
            'event' => 'payments_callback_received',
            'callback_reference' => $callbackData['reference'] ?? null,
            'callback_status' => $callbackData['status'] ?? null,
            'callback_amount' => $callbackData['amount'] ?? null,
            'callback_transaction_id' => $callbackData['transactionid'] ?? null,
            'callback_payment_system' => $callbackData['paymentsystem'] ?? null,
            'payment_id' => $payment?->id,
            'order_id' => $payment?->order_id,
            'order_number' => $payment?->order?->order_number,
            'user_id' => $payment?->user_id,
            'amount' => $payment?->amount,
            'currency' => $payment?->currency,
            'payment_status' => $payment?->status,
            'received_at' => now()->toISOString(),
            'context' => [
                'ip' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'signature' => request()->header('X-Signature') ? 'present' : 'missing',
                'callback_data' => $callbackData,
            ]
        ]);

        // Track callback processing time if payment exists
        if ($payment && $payment->created_at) {
            $processingTime = now()->diffInSeconds($payment->created_at);
            $this->recordProcessingTime('payment_callback', $processingTime);
        }
    }

    /**
     * Track payment failed event
     */
    public function paymentFailed(Payment $payment, string $reason = null): void
    {
        // Increment counters
        $this->incrementCounter('payments_failed');
        $this->incrementCounter("payments_failed_method_{$payment->payment_method}");
        
        if ($reason) {
            $reasonSlug = str_slug($reason, '_');
            $this->incrementCounter("payments_failed_reason_{$reasonSlug}");
        }

        // Log structured event
        Log::warning('payment.failed', [
            'event' => 'payments_failed',
            'payment_id' => $payment->id,
            'reference' => $payment->reference,
            'order_id' => $payment->order_id,
            'order_number' => $payment->order?->order_number,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'user_id' => $payment->user_id,
            'payment_method' => $payment->payment_method,
            'payment_gateway' => $payment->payment_gateway,
            'ebilling_id' => $payment->ebilling_id,
            'transaction_id' => $payment->transaction_id,
            'failure_reason' => $reason,
            'gateway_response' => $payment->gateway_response,
            'failed_at' => now()->toISOString(),
            'attempts_count' => $this->getPaymentAttemptsCount($payment->order_id),
            'context' => [
                'user_agent' => request()->header('User-Agent'),
                'ip' => request()->ip(),
                'session_id' => session()->getId(),
            ]
        ]);

        // Update failure rate metrics
        $this->updateDailyMetric('payments_failed', now(), 1);
    }

    /**
     * Track invoice generated event
     */
    public function invoiceGenerated(Order $order, float $generationTimeMs = null): void
    {
        // Increment counters
        $this->incrementCounter('invoice_generated');
        $this->incrementCounter("invoice_generated_type_{$order->type}");

        // Log structured event
        Log::info('invoice.generated', [
            'event' => 'invoice_generated',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'type' => $order->type,
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'user_id' => $order->user_id,
            'product_id' => $order->product_id,
            'generation_time_ms' => $generationTimeMs,
            'generated_at' => now()->toISOString(),
            'order_paid_at' => $order->paid_at?->toISOString(),
            'days_since_payment' => $order->paid_at 
                ? now()->diffInDays($order->paid_at)
                : null,
            'context' => [
                'user_agent' => request()->header('User-Agent'),
                'ip' => request()->ip(),
                'request_path' => request()->path(),
            ]
        ]);

        // Track generation performance
        if ($generationTimeMs) {
            $this->recordProcessingTime('invoice_generation', $generationTimeMs / 1000);
        }

        // Update daily metrics
        $this->updateDailyMetric('invoice_generated', now(), 1);
    }

    /**
     * Get application counters
     */
    public function getCounters(): array
    {
        $counters = [
            'orders_created' => $this->getCounter('orders_created'),
            'orders_paid' => $this->getCounter('orders_paid'),
            'payments_callback_received' => $this->getCounter('payments_callback_received'),
            'payments_failed' => $this->getCounter('payments_failed'),
            'invoice_generated' => $this->getCounter('invoice_generated'),
        ];

        // Add type-specific counters
        $types = ['lottery', 'direct'];
        foreach ($types as $type) {
            $counters["orders_created_type_{$type}"] = $this->getCounter("orders_created_type_{$type}");
            $counters["orders_paid_type_{$type}"] = $this->getCounter("orders_paid_type_{$type}");
        }

        // Add payment method counters
        $methods = ['airtel_money', 'moov_money'];
        foreach ($methods as $method) {
            $counters["payments_failed_method_{$method}"] = $this->getCounter("payments_failed_method_{$method}");
        }

        return $counters;
    }

    /**
     * Get metrics for a specific date range
     */
    public function getMetrics(Carbon $from = null, Carbon $to = null): array
    {
        $from = $from ?? now()->subDays(7);
        $to = $to ?? now();

        return [
            'counters' => $this->getCounters(),
            'daily_metrics' => $this->getDailyMetrics($from, $to),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'conversion_metrics' => $this->getConversionMetrics($from, $to),
            'period' => [
                'from' => $from->toISOString(),
                'to' => $to->toISOString(),
                'days' => $from->diffInDays($to) + 1,
            ]
        ];
    }

    /**
     * Reset all counters (useful for testing or maintenance)
     */
    public function resetCounters(): void
    {
        $pattern = self::METRICS_CACHE_PREFIX . 'counter:*';
        $keys = Cache::getRedis()->keys($pattern);
        
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }

        Log::info('metrics.counters.reset', [
            'event' => 'counters_reset',
            'reset_at' => now()->toISOString(),
            'keys_cleared' => count($keys ?? []),
        ]);
    }

    /**
     * Increment a counter
     */
    private function incrementCounter(string $name): int
    {
        $key = self::METRICS_CACHE_PREFIX . "counter:{$name}";
        return Cache::increment($key, 1);
    }

    /**
     * Get counter value
     */
    private function getCounter(string $name): int
    {
        $key = self::METRICS_CACHE_PREFIX . "counter:{$name}";
        return (int) Cache::get($key, 0);
    }

    /**
     * Update daily metrics
     */
    private function updateDailyMetric(string $metric, Carbon $date, float $value): void
    {
        $dateKey = $date->format('Y-m-d');
        $key = self::METRICS_CACHE_PREFIX . "daily:{$metric}:{$dateKey}";
        
        Cache::increment($key, $value);
        Cache::put($key, Cache::get($key, 0), self::COUNTER_CACHE_TTL * 24 * 60); // 24 hours in minutes
    }

    /**
     * Get daily metrics for date range
     */
    private function getDailyMetrics(Carbon $from, Carbon $to): array
    {
        $metrics = ['orders_created', 'orders_paid', 'payments_failed', 'invoice_generated'];
        $result = [];

        $current = $from->copy();
        while ($current <= $to) {
            $dateKey = $current->format('Y-m-d');
            $dayMetrics = ['date' => $dateKey];
            
            foreach ($metrics as $metric) {
                $key = self::METRICS_CACHE_PREFIX . "daily:{$metric}:{$dateKey}";
                $dayMetrics[$metric] = (int) Cache::get($key, 0);
            }
            
            $result[] = $dayMetrics;
            $current->addDay();
        }

        return $result;
    }

    /**
     * Record processing time
     */
    private function recordProcessingTime(string $operation, float $timeSeconds): void
    {
        $key = self::METRICS_CACHE_PREFIX . "performance:{$operation}";
        
        // Store last 100 processing times for averaging
        $times = Cache::get($key, []);
        $times[] = $timeSeconds;
        
        if (count($times) > 100) {
            $times = array_slice($times, -100);
        }
        
        Cache::put($key, $times, self::COUNTER_CACHE_TTL);
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        $operations = ['payment_callback', 'invoice_generation'];
        $metrics = [];

        foreach ($operations as $operation) {
            $key = self::METRICS_CACHE_PREFIX . "performance:{$operation}";
            $times = Cache::get($key, []);
            
            if (!empty($times)) {
                $metrics[$operation] = [
                    'avg_time_seconds' => round(array_sum($times) / count($times), 3),
                    'min_time_seconds' => round(min($times), 3),
                    'max_time_seconds' => round(max($times), 3),
                    'samples_count' => count($times),
                ];
            } else {
                $metrics[$operation] = [
                    'avg_time_seconds' => 0,
                    'min_time_seconds' => 0,
                    'max_time_seconds' => 0,
                    'samples_count' => 0,
                ];
            }
        }

        return $metrics;
    }

    /**
     * Add to revenue metrics
     */
    private function addToRevenueMetrics(float $amount): void
    {
        $key = self::METRICS_CACHE_PREFIX . 'revenue:total';
        Cache::increment($key, $amount);
    }

    /**
     * Get conversion metrics
     */
    private function getConversionMetrics(Carbon $from, Carbon $to): array
    {
        $ordersCreated = $this->getCounter('orders_created');
        $ordersPaid = $this->getCounter('orders_paid');
        $paymentsFailed = $this->getCounter('payments_failed');
        
        $conversionRate = $ordersCreated > 0 ? round(($ordersPaid / $ordersCreated) * 100, 2) : 0;
        $failureRate = ($ordersPaid + $paymentsFailed) > 0 
            ? round(($paymentsFailed / ($ordersPaid + $paymentsFailed)) * 100, 2) 
            : 0;

        return [
            'orders_to_payment_conversion_rate' => $conversionRate,
            'payment_failure_rate' => $failureRate,
            'total_revenue' => (float) Cache::get(self::METRICS_CACHE_PREFIX . 'revenue:total', 0),
        ];
    }

    /**
     * Get payment attempts count for an order
     */
    private function getPaymentAttemptsCount(?int $orderId): int
    {
        if (!$orderId) {
            return 0;
        }

        return Payment::where('order_id', $orderId)->count();
    }

    /**
     * Log a custom business metric
     */
    public function logCustomMetric(string $event, array $data): void
    {
        // Increment counter if it's a countable event
        if (str_ends_with($event, '_count') || str_ends_with($event, '_total')) {
            $this->incrementCounter($event);
        }

        // Log structured event
        Log::info("custom.{$event}", array_merge([
            'event' => $event,
            'logged_at' => now()->toISOString(),
        ], $data));
    }

    /**
     * Health check for metrics system
     */
    public function healthCheck(): array
    {
        try {
            // Test cache connectivity
            $testKey = self::METRICS_CACHE_PREFIX . 'health_check';
            Cache::put($testKey, 'ok', 60);
            $cacheValue = Cache::get($testKey);
            Cache::forget($testKey);

            // Test counter increment
            $testCounter = 'test_counter_' . uniqid();
            $this->incrementCounter($testCounter);
            $counterValue = $this->getCounter($testCounter);

            return [
                'status' => 'healthy',
                'cache_connectivity' => $cacheValue === 'ok',
                'counter_functionality' => $counterValue === 1,
                'current_counters' => count($this->getCounters()),
                'checked_at' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('metrics.health_check.failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'checked_at' => now()->toISOString(),
            ];
        }
    }
}