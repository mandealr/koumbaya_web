<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;

class PaymentStatusMapper
{
    /**
     * Gateway status mapping configuration
     */
    private static array $gatewayStatusMap = [
        // Success statuses
        'success' => PaymentStatus::PAID,
        'paid' => PaymentStatus::PAID,
        'completed' => PaymentStatus::PAID,
        'confirmed' => PaymentStatus::PAID,
        'successful' => PaymentStatus::PAID,
        'approved' => PaymentStatus::PAID,
        
        // Failed statuses
        'failed' => PaymentStatus::FAILED,
        'cancelled' => PaymentStatus::FAILED,
        'rejected' => PaymentStatus::FAILED,
        'error' => PaymentStatus::FAILED,
        'declined' => PaymentStatus::FAILED,
        'denied' => PaymentStatus::FAILED,
        'invalid' => PaymentStatus::FAILED,
        
        // Pending statuses
        'pending' => PaymentStatus::PENDING,
        'processing' => PaymentStatus::PENDING,
        'initiated' => PaymentStatus::PENDING,
        'waiting' => PaymentStatus::PENDING,
        'in_progress' => PaymentStatus::PENDING,
        
        // Expired statuses
        'expired' => PaymentStatus::EXPIRED,
        'timeout' => PaymentStatus::EXPIRED,
        'timed_out' => PaymentStatus::EXPIRED,
    ];

    /**
     * Map gateway status to internal payment status
     */
    public static function fromGateway(string $gatewayStatus): PaymentStatus
    {
        $normalizedStatus = strtolower(trim($gatewayStatus));
        
        $mappedStatus = self::$gatewayStatusMap[$normalizedStatus] ?? PaymentStatus::PENDING;
        
        Log::info('Payment status mapped from gateway', [
            'gateway_status' => $gatewayStatus,
            'normalized_status' => $normalizedStatus,
            'mapped_status' => $mappedStatus->value,
        ]);

        return $mappedStatus;
    }

    /**
     * Map payment status to corresponding order status
     */
    public static function toOrderStatus(PaymentStatus $paymentStatus): OrderStatus
    {
        $orderStatus = match($paymentStatus) {
            PaymentStatus::PAID => OrderStatus::PAID,
            PaymentStatus::FAILED => OrderStatus::FAILED,
            PaymentStatus::EXPIRED => OrderStatus::FAILED,
            PaymentStatus::PENDING => OrderStatus::AWAITING_PAYMENT,
        };

        Log::info('Order status derived from payment status', [
            'payment_status' => $paymentStatus->value,
            'order_status' => $orderStatus->value,
        ]);

        return $orderStatus;
    }

    /**
     * Check if payment status transition is valid
     */
    public static function isValidTransition(PaymentStatus $from, PaymentStatus $to): bool
    {
        $allowedTransitions = [
            PaymentStatus::PENDING->value => [
                PaymentStatus::PAID->value,
                PaymentStatus::FAILED->value,
                PaymentStatus::EXPIRED->value,
            ],
            // Final states cannot transition
            PaymentStatus::PAID->value => [],
            PaymentStatus::FAILED->value => [],
            PaymentStatus::EXPIRED->value => [],
        ];

        $isValid = in_array($to->value, $allowedTransitions[$from->value] ?? []);

        if (!$isValid) {
            Log::warning('Invalid payment status transition attempted', [
                'from_status' => $from->value,
                'to_status' => $to->value,
            ]);
        }

        return $isValid;
    }

    /**
     * Check if order status transition is valid
     */
    public static function isValidOrderTransition(OrderStatus $from, OrderStatus $to): bool
    {
        $allowedTransitions = [
            OrderStatus::PENDING->value => [
                OrderStatus::AWAITING_PAYMENT->value,
                OrderStatus::CANCELLED->value,
            ],
            OrderStatus::AWAITING_PAYMENT->value => [
                OrderStatus::PAID->value,
                OrderStatus::FAILED->value,
                OrderStatus::CANCELLED->value,
                OrderStatus::EXPIRED->value,
            ],
            OrderStatus::PAID->value => [
                OrderStatus::FULFILLED->value,
                OrderStatus::REFUNDED->value,
            ],
            OrderStatus::FULFILLED->value => [
                OrderStatus::REFUNDED->value,
            ],
            // Final states that cannot transition
            OrderStatus::FAILED->value => [],
            OrderStatus::CANCELLED->value => [],
            OrderStatus::REFUNDED->value => [],
        ];

        $isValid = in_array($to->value, $allowedTransitions[$from->value] ?? []);

        if (!$isValid) {
            Log::warning('Invalid order status transition attempted', [
                'from_status' => $from->value,
                'to_status' => $to->value,
            ]);
        }

        return $isValid;
    }

    /**
     * Get status message for API responses
     */
    public static function getStatusMessage(PaymentStatus $status, ?OrderStatus $orderStatus = null): string
    {
        $baseMessage = $status->message();

        if ($orderStatus) {
            $contextualMessages = [
                PaymentStatus::PAID->value => [
                    OrderStatus::PAID->value => 'Paiement confirmé - Commande en cours de traitement',
                    OrderStatus::FULFILLED->value => 'Paiement confirmé - Commande livrée avec succès',
                ],
                PaymentStatus::FAILED->value => [
                    OrderStatus::FAILED->value => 'Paiement échoué - Commande annulée',
                ],
                PaymentStatus::PENDING->value => [
                    OrderStatus::AWAITING_PAYMENT->value => 'Paiement en cours - Vérifiez votre téléphone',
                ],
            ];

            return $contextualMessages[$status->value][$orderStatus->value] ?? $baseMessage;
        }

        return $baseMessage;
    }

    /**
     * Get all gateway status mappings for documentation/debugging
     */
    public static function getGatewayMappings(): array
    {
        return array_map(fn($status) => $status->value, self::$gatewayStatusMap);
    }

    /**
     * Add custom gateway status mapping
     */
    public static function addGatewayMapping(string $gatewayStatus, PaymentStatus $paymentStatus): void
    {
        $normalizedStatus = strtolower(trim($gatewayStatus));
        self::$gatewayStatusMap[$normalizedStatus] = $paymentStatus;
        
        Log::info('Custom gateway status mapping added', [
            'gateway_status' => $gatewayStatus,
            'payment_status' => $paymentStatus->value,
        ]);
    }
}