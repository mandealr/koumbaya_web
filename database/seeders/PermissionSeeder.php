<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permissions organisées par ressource/module
        $permissions = [
            // === PRODUITS ===
            [
                'name' => 'products.create',
                'resource' => 'products',
                'action' => 'create',
                'description' => 'Créer des produits',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'products.read',
                'resource' => 'products',
                'action' => 'read',
                'description' => 'Voir les produits',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'products.update',
                'resource' => 'products',
                'action' => 'update',
                'description' => 'Modifier les produits',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'products.delete',
                'resource' => 'products',
                'action' => 'delete',
                'description' => 'Supprimer les produits',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'products.manage_all',
                'resource' => 'products',
                'action' => 'manage_all',
                'description' => 'Gérer tous les produits (admin)',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === TOMBOLAS ===
            [
                'name' => 'lotteries.create',
                'resource' => 'lotteries',
                'action' => 'create',
                'description' => 'Créer des tombolas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'lotteries.manage',
                'resource' => 'lotteries',
                'action' => 'manage',
                'description' => 'Gérer ses tombolas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'lotteries.participate',
                'resource' => 'lotteries',
                'action' => 'participate',
                'description' => 'Participer aux tombolas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'lotteries.view_results',
                'resource' => 'lotteries',
                'action' => 'view_results',
                'description' => 'Voir les résultats des tombolas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'lotteries.manage_all',
                'resource' => 'lotteries',
                'action' => 'manage_all',
                'description' => 'Gérer toutes les tombolas (admin)',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === UTILISATEURS ===
            [
                'name' => 'users.create',
                'resource' => 'users',
                'action' => 'create',
                'description' => 'Créer des utilisateurs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'users.read',
                'resource' => 'users',
                'action' => 'read',
                'description' => 'Voir les profils utilisateurs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'users.update',
                'resource' => 'users',
                'action' => 'update',
                'description' => 'Modifier les utilisateurs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'users.delete',
                'resource' => 'users',
                'action' => 'delete',
                'description' => 'Supprimer les utilisateurs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'users.manage_profile',
                'resource' => 'users',
                'action' => 'manage_profile',
                'description' => 'Gérer son propre profil',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === COMMANDES ===
            [
                'name' => 'orders.view_own',
                'resource' => 'orders',
                'action' => 'view_own',
                'description' => 'Voir ses propres commandes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'orders.manage_own',
                'resource' => 'orders',
                'action' => 'manage_own',
                'description' => 'Gérer ses commandes vendeur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'orders.manage_all',
                'resource' => 'orders',
                'action' => 'manage_all',
                'description' => 'Gérer toutes les commandes',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === FINANCES ===
            [
                'name' => 'finances.view_own',
                'resource' => 'finances',
                'action' => 'view_own',
                'description' => 'Voir ses propres finances',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'finances.manage_own',
                'resource' => 'finances',
                'action' => 'manage_own',
                'description' => 'Gérer ses finances (retraits, etc.)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'finances.view_all',
                'resource' => 'finances',
                'action' => 'view_all',
                'description' => 'Voir toutes les finances',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'finances.manage_all',
                'resource' => 'finances',
                'action' => 'manage_all',
                'description' => 'Gérer toutes les finances',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === SUPPORT CLIENT ===
            [
                'name' => 'support.handle_tickets',
                'resource' => 'support',
                'action' => 'handle_tickets',
                'description' => 'Traiter les tickets de support',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'support.manage_disputes',
                'resource' => 'support',
                'action' => 'manage_disputes',
                'description' => 'Gérer les litiges',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'support.process_refunds',
                'resource' => 'support',
                'action' => 'process_refunds',
                'description' => 'Traiter les remboursements',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === MODÉRATION ===
            [
                'name' => 'moderation.review_content',
                'resource' => 'moderation',
                'action' => 'review_content',
                'description' => 'Modérer le contenu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'moderation.verify_users',
                'resource' => 'moderation',
                'action' => 'verify_users',
                'description' => 'Vérifier les utilisateurs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'moderation.handle_reports',
                'resource' => 'moderation',
                'action' => 'handle_reports',
                'description' => 'Traiter les signalements',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === SYSTÈME ===
            [
                'name' => 'system.configure',
                'resource' => 'system',
                'action' => 'configure',
                'description' => 'Configurer le système',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'system.monitor',
                'resource' => 'system',
                'action' => 'monitor',
                'description' => 'Surveiller le système',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'system.backup',
                'resource' => 'system',
                'action' => 'backup',
                'description' => 'Gérer les sauvegardes',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === RÔLES ET PERMISSIONS ===
            [
                'name' => 'roles.assign',
                'resource' => 'roles',
                'action' => 'assign',
                'description' => 'Attribuer des rôles',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'roles.manage',
                'resource' => 'roles',
                'action' => 'manage',
                'description' => 'Gérer les rôles et permissions',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === ANALYTICS ET REPORTING ===
            [
                'name' => 'analytics.view_own',
                'resource' => 'analytics',
                'action' => 'view_own',
                'description' => 'Voir ses propres statistiques',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'analytics.view_all',
                'resource' => 'analytics',
                'action' => 'view_all',
                'description' => 'Voir toutes les statistiques',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'reports.generate',
                'resource' => 'reports',
                'action' => 'generate',
                'description' => 'Générer des rapports',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === NOTIFICATIONS ===
            [
                'name' => 'notifications.send',
                'resource' => 'notifications',
                'action' => 'send',
                'description' => 'Envoyer des notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'notifications.manage',
                'resource' => 'notifications',
                'action' => 'manage',
                'description' => 'Gérer les notifications système',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Adapter pour la table 'privileges' existante et ajouter user_type_id
        $adaptedPermissions = array_map(function($permission) {
            // Déterminer le user_type_id selon la permission
            $userTypeId = $this->getPermissionUserType($permission['name']);
            
            return [
                'name' => $permission['name'],
                'description' => $permission['description'],
                'user_type_id' => $userTypeId,
                'created_at' => $permission['created_at'],
                'updated_at' => $permission['updated_at'],
            ];
        }, $permissions);

        // Insérer dans la table 'privileges' existante
        DB::table('privileges')->insert($adaptedPermissions);

        echo "✅ " . count($permissions) . " permissions créées avec succès.\n";
    }

    /**
     * Déterminer le user_type_id selon la permission
     */
    private function getPermissionUserType($permissionName)
    {
        // Permissions pour CUSTOMERS (user_type_id = 2)
        $customerPermissions = [
            'products.read',
            'lotteries.participate', 
            'lotteries.view_results',
            'users.manage_profile',
            'orders.view_own',
            'finances.view_own',
            'analytics.view_own',
            // Business permissions (mais restent customers)
            'products.create',
            'products.update',
            'products.delete', 
            'lotteries.create',
            'lotteries.manage',
            'orders.manage_own',
            'finances.manage_own'
        ];

        // Si c'est une permission customer, retourner user_type_id = 2
        if (in_array($permissionName, $customerPermissions)) {
            return 2; // CUSTOMER
        }

        // Toutes les autres permissions sont pour MANAGERS (user_type_id = 1)
        return 1; // MANAGER
    }
}