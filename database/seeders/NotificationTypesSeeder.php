<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationType;

class NotificationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notificationTypes = [
            // User notifications
            [
                'type_id' => 'ticket_purchase',
                'name' => 'Achat de ticket',
                'description' => 'Notification lors de l\'achat d\'un ticket',
                'enabled' => true,
                'category' => 'user'
            ],
            [
                'type_id' => 'lottery_win',
                'name' => 'Gain de tombola',
                'description' => 'Notification en cas de gain',
                'enabled' => true,
                'category' => 'user'
            ],
            [
                'type_id' => 'lottery_draw',
                'name' => 'Tirage de tombola',
                'description' => 'Notification lors d\'un tirage',
                'enabled' => true,
                'category' => 'user'
            ],
            [
                'type_id' => 'payment_success',
                'name' => 'Paiement réussi',
                'description' => 'Confirmation de paiement',
                'enabled' => true,
                'category' => 'user'
            ],
            [
                'type_id' => 'payment_failed',
                'name' => 'Paiement échoué',
                'description' => 'Échec de paiement',
                'enabled' => true,
                'category' => 'user'
            ],
            [
                'type_id' => 'refund_processed',
                'name' => 'Remboursement traité',
                'description' => 'Notification de remboursement',
                'enabled' => true,
                'category' => 'user'
            ],

            // Admin notifications
            [
                'type_id' => 'new_lottery',
                'name' => 'Nouvelle tombola',
                'description' => 'Notification de nouvelle tombola créée',
                'enabled' => true,
                'category' => 'admin'
            ],
            [
                'type_id' => 'lottery_completed',
                'name' => 'Tombola terminée',
                'description' => 'Notification de tombola terminée',
                'enabled' => true,
                'category' => 'admin'
            ],
            [
                'type_id' => 'refund_request',
                'name' => 'Demande de remboursement',
                'description' => 'Nouvelle demande de remboursement',
                'enabled' => true,
                'category' => 'admin'
            ],
            [
                'type_id' => 'payment_anomaly',
                'name' => 'Anomalie de paiement',
                'description' => 'Détection d\'anomalie de paiement',
                'enabled' => true,
                'category' => 'admin'
            ],
            [
                'type_id' => 'system_alert',
                'name' => 'Alerte système',
                'description' => 'Alertes système importantes',
                'enabled' => true,
                'category' => 'admin'
            ],

            // Merchant notifications
            [
                'type_id' => 'new_sale',
                'name' => 'Nouvelle vente',
                'description' => 'Notification de nouvelle vente',
                'enabled' => true,
                'category' => 'merchant'
            ],
            [
                'type_id' => 'lottery_milestone',
                'name' => 'Étape de tombola',
                'description' => 'Étapes importantes de tombola',
                'enabled' => true,
                'category' => 'merchant'
            ],
            [
                'type_id' => 'product_review',
                'name' => 'Avis produit',
                'description' => 'Nouveaux avis sur les produits',
                'enabled' => false,
                'category' => 'merchant'
            ]
        ];

        foreach ($notificationTypes as $type) {
            NotificationType::updateOrCreate(
                ['type_id' => $type['type_id']],
                $type
            );
        }
    }
}