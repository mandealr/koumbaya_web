<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case EXPIRED = 'expired';

    /**
     * Get human-readable label for the status
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::PAID => 'Payé',
            self::FAILED => 'Échec',
            self::EXPIRED => 'Expiré',
        };
    }

    /**
     * Get detailed message for the status
     */
    public function message(): string
    {
        return match($this) {
            self::PENDING => 'Paiement en cours de traitement',
            self::PAID => 'Paiement effectué avec succès',
            self::FAILED => 'Le paiement a échoué',
            self::EXPIRED => 'Le délai de paiement a expiré',
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
            self::EXPIRED
        ]);
    }

    /**
     * Check if status represents a successful state
     */
    public function isSuccessful(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Check if payment is still processable
     */
    public function isProcessable(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Get all statuses as array
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}