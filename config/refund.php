<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Refund Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration centralisée pour les politiques de remboursement
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Seuils et Limites
    |--------------------------------------------------------------------------
    */

    'thresholds' => [
        // Nombre minimum de participants par défaut pour les tombolas
        'min_participants_default' => env('REFUND_MIN_PARTICIPANTS_DEFAULT', 10),

        // Seuil de secours si aucune configuration n'est trouvée
        'min_participants_fallback' => 10,

        // Montant minimum pour un remboursement (en XAF)
        'min_refund_amount' => 100,

        // Montant maximum pour un remboursement (en XAF)
        'max_refund_amount' => 1000000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Frais de Remboursement
    |--------------------------------------------------------------------------
    */

    'fees' => [
        // Frais administratifs en pourcentage
        'admin_fee_percentage' => env('REFUND_ADMIN_FEE_PERCENTAGE', 5),

        // Frais administratifs fixes (en XAF)
        'admin_fee_fixed' => env('REFUND_ADMIN_FEE_FIXED', 0),

        // Montant minimum après frais
        'min_amount_after_fees' => 50,

        // Appliquer les frais administratifs
        'apply_admin_fees' => env('REFUND_APPLY_ADMIN_FEES', true),

        // Types de remboursements exempts de frais
        'fee_exempt_reasons' => [
            'technical_error',
            'admin_decision',
            'fraud',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Délais et Périodes
    |--------------------------------------------------------------------------
    */

    'timeouts' => [
        // Délai d'attente avant remboursement automatique après expiration (en heures)
        'auto_refund_delay_hours' => env('REFUND_AUTO_DELAY_HOURS', 24),
        
        // Délai maximum pour traiter un remboursement manuel (en heures)
        'manual_processing_timeout_hours' => 48,
        
        // Délai d'expiration des commandes non payées (en minutes)
        'order_expiry_minutes' => 60,
        
        // Fréquence de traitement automatique des remboursements (cron)
        'auto_processing_schedule' => '03:00', // 3h du matin
    ],

    /*
    |--------------------------------------------------------------------------
    | Méthodes de Remboursement
    |--------------------------------------------------------------------------
    */

    'methods' => [
        'default' => 'mobile_money',
        
        'available' => [
            'mobile_money' => [
                'name' => 'Mobile Money',
                'enabled' => true,
                'auto_processing' => true,
                'operators' => [
                    'airtel_money' => [
                        'name' => 'Airtel Money',
                        'shap_name' => 'airtelmoney',
                        'prefixes' => ['074', '077', '076'],
                        'enabled' => true,
                    ],
                    'moov_money' => [
                        'name' => 'Moov Money',
                        'shap_name' => 'moovmoney4',
                        'prefixes' => ['065', '062', '066', '060'],
                        'enabled' => true,
                    ],
                ],
            ],
            
            'wallet_credit' => [
                'name' => 'Crédit Portefeuille',
                'enabled' => true,
                'auto_processing' => true,
                'currency' => 'XAF',
            ],
            
            'bank_transfer' => [
                'name' => 'Virement Bancaire',
                'enabled' => true,
                'auto_processing' => false, // Nécessite traitement manuel
                'manual_verification' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Types et Raisons de Remboursement
    |--------------------------------------------------------------------------
    */

    'types' => [
        'automatic' => [
            'name' => 'Automatique',
            'requires_approval' => false,
            'auto_process' => true,
        ],
        
        'manual' => [
            'name' => 'Manuel',
            'requires_approval' => true,
            'auto_process' => false,
        ],
        
        'admin' => [
            'name' => 'Administratif',
            'requires_approval' => false,
            'auto_process' => true,
        ],
    ],

    'reasons' => [
        'lottery_cancelled' => 'Tombola annulée',
        'insufficient_participants' => 'Participants insuffisants',
        'product_unavailable' => 'Produit indisponible',
        'technical_error' => 'Erreur technique',
        'customer_request' => 'Demande client',
        'fraud' => 'Fraude détectée',
        'admin_decision' => 'Décision administrative',
        'other' => 'Autre raison',
    ],

    /*
    |--------------------------------------------------------------------------
    | Règles Automatiques
    |--------------------------------------------------------------------------
    */

    'auto_rules' => [
        // Remboursement automatique si participants insuffisants
        'insufficient_participants' => [
            'enabled' => env('REFUND_AUTO_INSUFFICIENT_PARTICIPANTS', true),
            'check_frequency' => 'daily', // daily, hourly
            'delay_hours' => 24, // Attendre 24h après expiration avant traitement automatique
            'allow_manual_before_delay' => true, // Permettre le traitement manuel avant le délai
        ],
        
        // Remboursement automatique des tombolas annulées
        'cancelled_lotteries' => [
            'enabled' => env('REFUND_AUTO_CANCELLED_LOTTERIES', true),
            'immediate' => true, // Traitement immédiat
        ],
        
        // Remboursement automatique des commandes expirées
        'expired_orders' => [
            'enabled' => env('REFUND_AUTO_EXPIRED_ORDERS', true),
            'check_frequency' => 'every_15_minutes',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'email' => [
            'enabled' => true,
            'templates' => [
                'refund_processed' => 'emails.refund-processed',
                'refund_rejected' => 'emails.refund-rejected',
                'refund_approved' => 'emails.refund-approved',
            ],
        ],
        
        'sms' => [
            'enabled' => true,
            'templates' => [
                'refund_completed' => 'Votre remboursement de {amount} FCFA a été traité avec succès. Réf: {reference}',
                'refund_rejected' => 'Votre demande de remboursement a été rejetée. Raison: {reason}',
            ],
        ],
        
        'push' => [
            'enabled' => true,
            'titles' => [
                'refund_completed' => 'Remboursement effectué',
                'refund_rejected' => 'Remboursement rejeté',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sécurité et Validation
    |--------------------------------------------------------------------------
    */

    'security' => [
        // Vérification du solde avant payout
        'check_balance_before_payout' => true,
        
        // Validation stricte des numéros de téléphone
        'strict_phone_validation' => true,
        
        // Double vérification pour les gros montants
        'double_check_threshold' => 100000, // 100,000 XAF
        
        // Limitation du nombre de remboursements par utilisateur par jour
        'daily_refund_limit_per_user' => 5,
        
        // Limitation du montant total de remboursements par utilisateur par jour
        'daily_amount_limit_per_user' => 500000, // 500,000 XAF
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging et Audit
    |--------------------------------------------------------------------------
    */

    'logging' => [
        // Activer les logs détaillés
        'detailed_logging' => env('REFUND_DETAILED_LOGGING', true),
        
        // Conserver les logs pendant X jours
        'log_retention_days' => 90,
        
        // Canaux de logs
        'channels' => ['daily', 'slack'], // Utiliser les canaux configurés dans logging.php
        
        // Logs d'audit pour les actions sensibles
        'audit_events' => [
            'refund_approved',
            'refund_rejected', 
            'refund_processed',
            'balance_insufficient',
            'api_error',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Intégrations Externes
    |--------------------------------------------------------------------------
    */

    'integrations' => [
        'shap' => [
            'enabled' => env('REFUND_SHAP_ENABLED', true),
            'timeout' => 60, // secondes
            'retry_attempts' => 3,
            'retry_delay' => 5, // secondes entre tentatives
        ],
        
        'webhook' => [
            'enabled' => env('REFUND_WEBHOOK_ENABLED', false),
            'url' => env('REFUND_WEBHOOK_URL'),
            'secret' => env('REFUND_WEBHOOK_SECRET'),
        ],
    ],
];