<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Koumbaya Marketplace Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique à la plateforme Koumbaya
    |
    */

    'ticket_calculation' => [
        
        /*
        |--------------------------------------------------------------------------
        | Taux de Commission Koumbaya
        |--------------------------------------------------------------------------
        |
        | Pourcentage prélevé par Koumbaya sur chaque produit
        | Valeur par défaut: 10% (0.10)
        |
        */
        'commission_rate' => env('KOUMBAYA_COMMISSION_RATE', 0.10),

        /*
        |--------------------------------------------------------------------------
        | Taux de Marge Koumbaya
        |--------------------------------------------------------------------------
        |
        | Pourcentage de marge supplémentaire pour les opérations Koumbaya
        | Valeur par défaut: 15% (0.15)
        |
        */
        'margin_rate' => env('KOUMBAYA_MARGIN_RATE', 0.15),

        /*
        |--------------------------------------------------------------------------
        | Nombre de Tickets par Défaut
        |--------------------------------------------------------------------------
        |
        | Nombre de tickets générés par défaut pour chaque tombola
        |
        */
        'default_tickets' => env('KOUMBAYA_DEFAULT_TICKETS', 500),

        /*
        |--------------------------------------------------------------------------
        | Limites du Nombre de Tickets
        |--------------------------------------------------------------------------
        |
        | Nombre minimum et maximum de tickets autorisés
        |
        */
        'min_tickets' => env('KOUMBAYA_MIN_TICKETS', 100),
        'max_tickets' => env('KOUMBAYA_MAX_TICKETS', 5000),

        /*
        |--------------------------------------------------------------------------
        | Prix de Ticket - Limites
        |--------------------------------------------------------------------------
        |
        | Prix minimum et maximum recommandés pour un ticket (en FCFA)
        |
        */
        'min_ticket_price' => env('KOUMBAYA_MIN_TICKET_PRICE', 200), // 200 FCFA
        'max_ticket_price' => env('KOUMBAYA_MAX_TICKET_PRICE', 50000), // 50,000 FCFA

        /*
        |--------------------------------------------------------------------------
        | Arrondissement des Prix
        |--------------------------------------------------------------------------
        |
        | Mode d'arrondissement: 'ceil', 'floor', 'round'
        | - ceil: Arrondi vers le haut (recommandé)
        | - floor: Arrondi vers le bas
        | - round: Arrondi mathématique
        |
        */
        'rounding_mode' => env('KOUMBAYA_ROUNDING_MODE', 'ceil'),
        
        /*
        |--------------------------------------------------------------------------
        | Arrondissement par Multiple
        |--------------------------------------------------------------------------
        |
        | Arrondir les prix à un multiple spécifique
        | Exemple: 25 arrondit à 25, 50, 75, 100, etc.
        | Valeur 0 ou null désactive cette fonction
        |
        */
        'round_to_multiple' => env('KOUMBAYA_ROUND_TO_MULTIPLE', 25),
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres de la Marketplace
    |--------------------------------------------------------------------------
    */
    'marketplace' => [

        /*
        |--------------------------------------------------------------------------
        | Prix Minimum Produit
        |--------------------------------------------------------------------------
        |
        | Prix minimum pour créer un produit (en FCFA)
        | Production: 1000 FCFA | Tests: 200 FCFA
        |
        */
        'min_product_price' => env('KOUMBAYA_MIN_PRODUCT_PRICE', 1000),

        /*
        |--------------------------------------------------------------------------
        | Devise
        |--------------------------------------------------------------------------
        */
        'currency' => env('KOUMBAYA_CURRENCY', 'FCFA'),
        'currency_symbol' => env('KOUMBAYA_CURRENCY_SYMBOL', 'FCFA'),
        
        /*
        |--------------------------------------------------------------------------
        | Pays d'opération
        |--------------------------------------------------------------------------
        */
        'country' => env('KOUMBAYA_COUNTRY', 'Gabon'),
        'country_code' => env('KOUMBAYA_COUNTRY_CODE', 'GA'),
        
        /*
        |--------------------------------------------------------------------------
        | Durée de tombola par défaut (en jours)
        |--------------------------------------------------------------------------
        */
        'default_lottery_duration' => env('KOUMBAYA_DEFAULT_LOTTERY_DURATION', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres d'Affichage
    |--------------------------------------------------------------------------
    */
    'display' => [
        
        /*
        |--------------------------------------------------------------------------
        | Nombre d'éléments par page
        |--------------------------------------------------------------------------
        */
        'products_per_page' => env('KOUMBAYA_PRODUCTS_PER_PAGE', 12),
        'lotteries_per_page' => env('KOUMBAYA_LOTTERIES_PER_PAGE', 12),
        
        /*
        |--------------------------------------------------------------------------
        | Images
        |--------------------------------------------------------------------------
        */
        'placeholder_image' => '/images/placeholder.jpg',
        'logo_url' => '/images/logo.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonctionnalités
    |--------------------------------------------------------------------------
    */
    'features' => [
        
        /*
        |--------------------------------------------------------------------------
        | Auto-calcul des prix de tickets
        |--------------------------------------------------------------------------
        */
        'auto_calculate_ticket_price' => env('KOUMBAYA_AUTO_CALCULATE_TICKET_PRICE', true),
        
        /*
        |--------------------------------------------------------------------------
        | Permettre aux marchands de modifier le nombre de tickets
        |--------------------------------------------------------------------------
        */
        'allow_custom_ticket_count' => env('KOUMBAYA_ALLOW_CUSTOM_TICKET_COUNT', true),
        
        /*
        |--------------------------------------------------------------------------
        | Configuration des Profils Vendeurs
        |--------------------------------------------------------------------------
        */
        'seller_profiles' => [
            'individual' => [
                'fixed_tickets' => 500, // Tickets fixes pour les vendeurs particuliers
                'can_customize_tickets' => false, // Ne peuvent pas modifier le nombre
                'min_product_price' => 100000, // Prix minimum produit (pour avoir ticket >= 200 FCFA)
                'lottery_duration' => [
                    'fixed' => 30, // Durée fixe de 30 jours
                    'can_customize' => false, // Ne peut pas modifier la durée
                    'min_days' => 30,
                    'max_days' => 30,
                ],
            ],
            'business' => [
                'fixed_tickets' => null, // Peut choisir le nombre de tickets
                'can_customize_tickets' => true, // Toutes les possibilités
                'min_product_price' => null, // Pas de limite minimum
                'lottery_duration' => [
                    'fixed' => null, // Pas de durée fixe
                    'can_customize' => true, // Peut configurer comme il veut
                    'min_days' => 1, // Minimum 1 jour
                    'max_days' => 60, // Maximum 60 jours
                ],
            ]
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Afficher les détails de calcul aux marchands
        |--------------------------------------------------------------------------
        */
        'show_calculation_details' => env('KOUMBAYA_SHOW_CALCULATION_DETAILS', true),
    ],

];