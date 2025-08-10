<?php

namespace App\Services;

class TicketPriceCalculator
{
    /**
     * Calcule automatiquement le prix d'un ticket de tombola
     * 
     * Formule: Prix ticket = (Prix de l'Article + (Prix de l'Article × Commission) + (Prix de l'Article × Marge)) / Nombre de Tickets
     * 
     * @param float $productPrice Prix de l'article
     * @param int $numberOfTickets Nombre total de tickets
     * @param float $commissionRate Taux de commission Koumbaya (par défaut 10%)
     * @param float $marginRate Taux de marge Koumbaya (par défaut 15%)
     * @return float Prix du ticket calculé
     */
    public static function calculateTicketPrice(
        float $productPrice,
        int $numberOfTickets = 1000,
        float $commissionRate = 0.10, // 10%
        float $marginRate = 0.15      // 15%
    ): float {
        // Validation des paramètres
        if ($productPrice <= 0) {
            throw new \InvalidArgumentException('Le prix du produit doit être supérieur à 0');
        }
        
        if ($numberOfTickets <= 0) {
            throw new \InvalidArgumentException('Le nombre de tickets doit être supérieur à 0');
        }
        
        if ($commissionRate < 0 || $marginRate < 0) {
            throw new \InvalidArgumentException('Les taux ne peuvent pas être négatifs');
        }

        // Calcul selon la formule
        $commission = $productPrice * $commissionRate;
        $margin = $productPrice * $marginRate;
        $totalAmount = $productPrice + $commission + $margin;
        
        $ticketPrice = $totalAmount / $numberOfTickets;
        
        // Arrondi à l'entier supérieur pour éviter les centimes
        return ceil($ticketPrice);
    }

    /**
     * Obtient les paramètres de calcul depuis la configuration
     * 
     * @return array
     */
    public static function getCalculationParameters(): array
    {
        return [
            'commission_rate' => config('koumbaya.ticket_calculation.commission_rate', 0.10),
            'margin_rate' => config('koumbaya.ticket_calculation.margin_rate', 0.15),
            'default_tickets' => config('koumbaya.ticket_calculation.default_tickets', 1000),
            'min_tickets' => config('koumbaya.ticket_calculation.min_tickets', 100),
            'max_tickets' => config('koumbaya.ticket_calculation.max_tickets', 5000),
        ];
    }

    /**
     * Calcule les détails complets du calcul pour affichage
     * 
     * @param float $productPrice
     * @param int $numberOfTickets
     * @param float $commissionRate
     * @param float $marginRate
     * @return array
     */
    public static function getCalculationDetails(
        float $productPrice,
        int $numberOfTickets = 1000,
        float $commissionRate = 0.10,
        float $marginRate = 0.15
    ): array {
        $commission = $productPrice * $commissionRate;
        $margin = $productPrice * $marginRate;
        $totalAmount = $productPrice + $commission + $margin;
        $ticketPrice = $totalAmount / $numberOfTickets;
        $finalTicketPrice = ceil($ticketPrice);

        return [
            'product_price' => $productPrice,
            'commission_rate' => $commissionRate,
            'margin_rate' => $marginRate,
            'commission_amount' => $commission,
            'margin_amount' => $margin,
            'total_amount' => $totalAmount,
            'number_of_tickets' => $numberOfTickets,
            'calculated_ticket_price' => $ticketPrice,
            'final_ticket_price' => $finalTicketPrice,
            'total_potential_revenue' => $finalTicketPrice * $numberOfTickets,
            'koumbaya_profit' => $commission + $margin,
            'merchant_revenue' => $productPrice,
        ];
    }

    /**
     * Valide si le prix calculé est raisonnable
     * 
     * @param float $ticketPrice
     * @return array
     */
    public static function validateTicketPrice(float $ticketPrice): array
    {
        $minPrice = config('koumbaya.ticket_calculation.min_ticket_price', 100); // 100 FCFA
        $maxPrice = config('koumbaya.ticket_calculation.max_ticket_price', 50000); // 50,000 FCFA
        
        $warnings = [];
        
        if ($ticketPrice < $minPrice) {
            $warnings[] = "Le prix du ticket ({$ticketPrice} FCFA) est très bas. Minimum recommandé: {$minPrice} FCFA";
        }
        
        if ($ticketPrice > $maxPrice) {
            $warnings[] = "Le prix du ticket ({$ticketPrice} FCFA) est très élevé. Maximum recommandé: {$maxPrice} FCFA";
        }
        
        return [
            'is_valid' => empty($warnings),
            'warnings' => $warnings
        ];
    }

    /**
     * Suggestions pour optimiser le prix du ticket
     * 
     * @param float $productPrice
     * @param float $currentTicketPrice
     * @return array
     */
    public static function getSuggestions(float $productPrice, float $currentTicketPrice): array
    {
        $suggestions = [];
        
        // Suggestion pour différents nombres de tickets
        $ticketOptions = [500, 750, 1000, 1250, 1500, 2000];
        
        foreach ($ticketOptions as $tickets) {
            $price = self::calculateTicketPrice($productPrice, $tickets);
            $suggestions[] = [
                'tickets' => $tickets,
                'price' => $price,
                'total_revenue' => $price * $tickets,
                'is_recommended' => $price >= 500 && $price <= 10000 // Prix recommandé entre 500 et 10,000 FCFA
            ];
        }
        
        return $suggestions;
    }
}