<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Privilege;
use App\Models\UserType;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Architecture à deux niveaux :
     * Les privilèges sont rattachés aux user_types (admin ou customer)
     * et ensuite assignés aux rôles via la table pivot role_privileges
     */
    public function run(): void
    {
        echo "🔄 Nettoyage des privilèges existants...\n";

        // Vider la table privileges et role_privileges (pivot)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('role_privileges')->truncate();
        Privilege::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "📝 Création des nouveaux privilèges...\n";

        // Les IDs sont fixes car on a recréé les user_types avec des IDs fixes
        $adminTypeId = 1;  // admin
        $customerTypeId = 2;  // customer

        // Permissions organisées par type d'utilisateur
        $permissions = [

            // === PERMISSIONS CUSTOMER TYPE (particulier, business_individual, business_enterprise) ===
            [
                'name' => 'products.browse',
                'description' => 'Parcourir et voir les articles',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'orders.create',
                'description' => 'Créer des commandes d\'achat',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'orders.view_own',
                'description' => 'Voir ses propres commandes',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'payments.make',
                'description' => 'Effectuer des paiements',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'lotteries.participate',
                'description' => 'Participer aux tirages spéciaux',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'profile.manage_own',
                'description' => 'Gérer son propre profil',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'notifications.view_own',
                'description' => 'Voir ses propres notifications',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Permissions vendeur (pour business_individual et business_enterprise)
            [
                'name' => 'products.create',
                'description' => 'Créer des articles',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'products.manage_own',
                'description' => 'Gérer ses propres articles',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'lotteries.create',
                'description' => 'Créer des tirages spéciaux',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'lotteries.manage_own',
                'description' => 'Gérer ses propres tirages spéciaux',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'orders.manage_sales',
                'description' => 'Gérer ses commandes de vente',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'orders.export_own',
                'description' => 'Exporter ses propres commandes',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'analytics.view_own',
                'description' => 'Voir ses propres statistiques de vente',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'finances.manage_own',
                'description' => 'Gérer ses propres finances',
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === PERMISSIONS ADMIN TYPE (agent, admin, superadmin) ===
            [
                'name' => 'users.manage_all',
                'description' => 'Gérer tous les utilisateurs',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'products.manage_all',
                'description' => 'Gérer tous les produits',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'lotteries.manage_all',
                'description' => 'Gérer toutes les loteries',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'orders.manage_all',
                'description' => 'Gérer toutes les commandes',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'orders.export_all',
                'description' => 'Exporter toutes les commandes',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'payments.manage_all',
                'description' => 'Gérer tous les paiements',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'refunds.process',
                'description' => 'Traiter les remboursements',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'analytics.view_all',
                'description' => 'Voir toutes les statistiques',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'reports.generate',
                'description' => 'Générer des rapports',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'system.configure',
                'description' => 'Configurer le système',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'roles.assign',
                'description' => 'Attribuer des rôles',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'privileges.manage',
                'description' => 'Gérer les privilèges',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'notifications.send_all',
                'description' => 'Envoyer des notifications à tous',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'support.handle_tickets',
                'description' => 'Traiter les tickets de support',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'moderation.review_content',
                'description' => 'Modérer le contenu',
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($permissions as $privilegeData) {
            Privilege::create($privilegeData);
        }

        echo "✅ " . count($permissions) . " privilèges créés avec succès.\n";
        echo "   - " . count(array_filter($permissions, fn($p) => $p['user_type_id'] === $customerTypeId)) . " privilèges pour CUSTOMER type (ID: 2)\n";
        echo "   - " . count(array_filter($permissions, fn($p) => $p['user_type_id'] === $adminTypeId)) . " privilèges pour ADMIN type (ID: 1)\n";
    }
}