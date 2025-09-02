<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case FULFILLED = 'fulfilled';
    case REFUNDED = 'refunded';
    case EXPIRED = 'expired';

    /**
     * Get human-readable label for the status
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::AWAITING_PAYMENT => 'En attente de paiement',
            self::PAID => 'Payé',
            self::FAILED => 'Échec',
            self::CANCELLED => 'Annulé',
            self::FULFILLED => 'Livré',
            self::REFUNDED => 'Remboursé',
            self::EXPIRED => 'Expiré',
        };
    }

    /**
     * Get detailed message for the status
     */
    public function message(): string
    {
        return match($this) {
            self::PENDING => 'Commande créée et en attente de traitement',
            self::AWAITING_PAYMENT => 'Commande en attente du paiement',
            self::PAID => 'Commande payée avec succès',
            self::FAILED => 'Le paiement de la commande a échoué',
            self::CANCELLED => 'Commande annulée',
            self::FULFILLED => 'Commande livrée avec succès',
            self::REFUNDED => 'Commande remboursée',
            self::EXPIRED => 'Commande expirée après 1 heure sans paiement',
        };
    }

    /**
     * Check if status represents a final state
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::PAID,
            self::FAILED,
            self::CANCELLED,
            self::FULFILLED,
            self::REFUNDED,
            self::EXPIRED
        ]);
    }

    /**
     * Check if status represents a successful state
     */
    public function isSuccessful(): bool
    {
        return in_array($this, [
            self::PAID,
            self::FULFILLED
        ]);
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::AWAITING_PAYMENT
        ]);
    }

    /**
     * Check if order can be fulfilled
     */
    public function canBeFulfilled(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Get all statuses as array
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}