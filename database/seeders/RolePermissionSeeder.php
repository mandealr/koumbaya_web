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
     * Associe les privilèges aux rôles selon la structure BD optimisée Koumbaya
     */
    public function run(): void
    {
        // Les rôles seront récupérés dynamiquement dans la boucle

        // Définir les associations rôles => privilèges (noms compatibles code existant)
        $rolePrivileges = [
            
            // === PARTICULIER ===
            'Particulier' => [
                'products.browse',
                'orders.create', 
                'orders.view_own',
                'payments.make',
                'lotteries.participate',
                'profile.manage_own',
                'notifications.view_own',
            ],
            
            // === BUSINESS ===
            'Business' => [
                // Toutes les permissions particulier
                'products.browse',
                'orders.create', 
                'orders.view_own',
                'payments.make',
                'lotteries.participate',
                'profile.manage_own',
                'notifications.view_own',
                
                // + Permissions business
                'products.create',
                'products.manage_own',
                'lotteries.create',
                'lotteries.manage_own',
                'orders.manage_sales',
                'orders.export_own',
                'analytics.view_own',
                'finances.manage_own',
            ],
            
            // === AGENT ===
            'Agent' => [
                'users.manage_all',
                'support.handle_tickets',
                'moderation.review_content',
                'refunds.process',
                'orders.manage_all',
                'products.manage_all',
            ],
            
            // === ADMIN ===
            'Admin' => [
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
            
            // === SUPER ADMIN ===
            'Super Admin' => '*', // Tous les privilèges
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
     * Assigner les privilèges à un rôle avec firstOrCreate (évite les doublons)
     */
    private function assignPrivilegesToRole($roleId, $privilegeIds)
    {
        foreach ($privilegeIds as $privilegeId) {
            RolePrivilege::firstOrCreate(
                [
                    'role_id' => $roleId,
                    'privilege_id' => $privilegeId,
                ],
                [
                    'role_id' => $roleId,
                    'privilege_id' => $privilegeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}