<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Privilege;
use App\Models\RolePrivilege;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Architecture à deux niveaux :
     * Associe les privilèges aux rôles (Niveau 2)
     * selon leur user_type (Niveau 1)
     */
    public function run(): void
    {
        // Les rôles seront récupérés dynamiquement dans la boucle

        // Définir les associations rôles => privilèges
        $rolePrivileges = [

            // === CUSTOMER TYPE ROLES ===

            // PARTICULIER (client acheteur uniquement)
            'particulier' => [
                'products.browse',
                'orders.create',
                'orders.view_own',
                'payments.make',
                'lotteries.participate',
                'profile.manage_own',
                'notifications.view_own',
            ],

            // BUSINESS INDIVIDUAL (vendeur individuel avec contraintes)
            'business_individual' => [
                // Permissions particulier
                'products.browse',
                'orders.create',
                'orders.view_own',
                'payments.make',
                'lotteries.participate',
                'profile.manage_own',
                'notifications.view_own',

                // + Permissions vendeur
                'products.create',
                'products.manage_own',
                'lotteries.create',
                'lotteries.manage_own',
                'orders.manage_sales',
                'orders.export_own',
                'analytics.view_own',
                'finances.manage_own',
            ],

            // BUSINESS ENTERPRISE (vendeur professionnel sans contraintes)
            'business_enterprise' => [
                // Permissions particulier
                'products.browse',
                'orders.create',
                'orders.view_own',
                'payments.make',
                'lotteries.participate',
                'profile.manage_own',
                'notifications.view_own',

                // + Permissions vendeur professionnel
                'products.create',
                'products.manage_own',
                'lotteries.create',
                'lotteries.manage_own',
                'orders.manage_sales',
                'orders.export_own',
                'analytics.view_own',
                'finances.manage_own',
            ],

            // === ADMIN TYPE ROLES ===

            // AGENT (support et modération basique)
            'agent' => [
                'users.manage_all',
                'support.handle_tickets',
                'moderation.review_content',
                'refunds.process',
                'orders.manage_all',
                'products.manage_all',
            ],

            // ADMIN (gestion complète de la plateforme)
            'admin' => [
                // Toutes les permissions agent
                'users.manage_all',
                'support.handle_tickets',
                'moderation.review_content',
                'refunds.process',
                'orders.manage_all',
                'products.manage_all',

                // + Permissions admin
                'lotteries.manage_all',
                'orders.export_all',
                'payments.manage_all',
                'analytics.view_all',
                'reports.generate',
                'notifications.send_all',
                'roles.assign',
            ],

            // SUPERADMIN (accès système complet)
            'superadmin' => '*', // Tous les privilèges
        ];

        // Traiter les associations
        foreach ($rolePrivileges as $roleName => $privileges) {
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                echo "⚠️  Rôle '$roleName' non trouvé, ignoré.\n";
                continue;
            }

            // Super Admin = tous les privilèges
            if ($privileges === '*') {
                $allPrivileges = Privilege::pluck('id')->toArray();
                $this->assignPrivilegesToRole($role->id, $allPrivileges);
                echo "✅ {$roleName} : TOUS les privilèges assignés (" . count($allPrivileges) . ")\n";
                continue;
            }

            // Récupérer les IDs des privilèges
            $privilegeIds = Privilege::whereIn('name', $privileges)->pluck('id')->toArray();

            if (!empty($privilegeIds)) {
                $this->assignPrivilegesToRole($role->id, $privilegeIds);
                echo "✅ {$roleName} : " . count($privilegeIds) . " privilèges assignés\n";
            }
        }

        echo "\n🎯 Attribution des privilèges terminée pour la structure BD optimisée.\n";
    }

    /**
     * Assigner les privilèges à un rôle
     */
    private function assignPrivilegesToRole($roleId, $privilegeIds)
    {
        foreach ($privilegeIds as $privilegeId) {
            RolePrivilege::create([
                'role_id' => $roleId,
                'privilege_id' => $privilegeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}