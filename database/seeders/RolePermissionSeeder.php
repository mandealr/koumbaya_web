<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Associe les permissions aux rôles selon le système hybride Koumbaya
     */
    public function run(): void
    {
        // Définir les associations rôles => permissions
        $rolePermissions = [
            
            // === PARTICULIER (Base obligatoire) ===
            'particulier' => [
                // Participation et achat
                'products.read',
                'lotteries.participate',
                'lotteries.view_results',
                
                // Gestion personnelle
                'users.manage_profile',
                'orders.view_own',
                'finances.view_own',
                'analytics.view_own',
            ],
            
            // === BUSINESS (Particulier + Vendeur) ===
            'business' => [
                // Héritage complet du particulier (sera ajouté automatiquement)
                // + Permissions vendeur
                'products.create',
                'products.update', 
                'products.delete',
                
                'lotteries.create',
                'lotteries.manage',
                
                'orders.manage_own',
                'finances.manage_own',
                
                'analytics.view_own', // Déjà dans particulier mais pour clarté
            ],
            
            // === AGENT (Support client) ===
            'agent' => [
                // Support et assistance
                'support.handle_tickets',
                'support.manage_disputes',
                'support.process_refunds',
                
                // Modération basique
                'moderation.review_content',
                'moderation.handle_reports',
                
                // Lecture utilisateurs (pour support)
                'users.read',
                'products.read',
                'orders.view_own', // Leurs tickets
            ],
            
            // === AGENT BACK OFFICE (Agent + Gestion avancée) ===
            'agent-back-office' => [
                // Héritage agent (sera ajouté automatiquement)
                // + Gestions avancées
                'users.create', // Créer des comptes pour support
                'users.update', // Modifier pour assistance
                
                'moderation.verify_users',
                
                'finances.view_all', // Vue générale pour reporting
                'analytics.view_all',
                'reports.generate',
                
                'orders.manage_all', // Gestion globale commandes
            ],
            
            // === ADMIN (Gestion complète plateforme) ===
            'admin' => [
                // Gestion utilisateurs complète
                'users.create',
                'users.read', 
                'users.update',
                'users.delete',
                
                // Gestion contenu complète
                'products.manage_all',
                'lotteries.manage_all',
                'orders.manage_all',
                
                // Finances complètes
                'finances.view_all',
                'finances.manage_all',
                
                // Support et modération avancés
                'support.handle_tickets',
                'support.manage_disputes', 
                'support.process_refunds',
                'moderation.review_content',
                'moderation.verify_users',
                'moderation.handle_reports',
                
                // Analytics et reporting
                'analytics.view_all',
                'reports.generate',
                
                // Attribution de rôles
                'roles.assign',
                
                // Notifications
                'notifications.send',
                'notifications.manage',
                
                // Système (limité)
                'system.monitor',
            ],
            
            // === SUPER ADMIN (Accès total) ===
            'super-admin' => [
                // Toutes les permissions (sera géré spécialement)
                '*' // Wildcard pour toutes permissions
            ],
        ];

        // Traiter les associations (adapter aux tables existantes)
        foreach ($rolePermissions as $roleName => $permissions) {
            // Rechercher le rôle par nom dans la table 'roles' existante
            $role = DB::table('roles')->where('name', $this->getRoleDisplayName($roleName))->first();
            
            if (!$role) {
                echo "⚠️  Rôle '$roleName' non trouvé, ignoré.\n";
                continue;
            }

            // Super Admin = toutes les permissions
            if ($roleName === 'super-admin') {
                $allPermissions = DB::table('privileges')->pluck('id')->toArray();
                $this->assignPermissionsToRole($role->id, $allPermissions);
                echo "✅ Super Admin : TOUTES les permissions assignées.\n";
                continue;
            }

            // Gestion de l'héritage pour les rôles combinés
            $finalPermissions = $this->resolvePermissionsWithInheritance($roleName, $permissions, $rolePermissions);
            
            // Récupérer les IDs des permissions dans la table 'privileges'
            $permissionIds = DB::table('privileges')
                ->whereIn('name', $finalPermissions)
                ->pluck('id')
                ->toArray();

            if (!empty($permissionIds)) {
                $this->assignPermissionsToRole($role->id, $permissionIds);
                echo "✅ Rôle '$roleName' : " . count($permissionIds) . " permissions assignées.\n";
            }
        }

        echo "\n🎯 Attribution des permissions terminée selon le système hybride Koumbaya.\n";
    }

    /**
     * Résoudre les permissions avec héritage
     */
    private function resolvePermissionsWithInheritance($roleSlug, $permissions, $allRolePermissions)
    {
        $finalPermissions = $permissions;

        // Business hérite de particulier
        if ($roleSlug === 'business') {
            $finalPermissions = array_merge(
                $allRolePermissions['particulier'], 
                $permissions
            );
        }

        // Agent Back Office hérite d'Agent
        if ($roleSlug === 'agent-back-office') {
            $finalPermissions = array_merge(
                $allRolePermissions['agent'],
                $permissions
            );
        }

        // Admin hérite d'Agent Back Office
        if ($roleSlug === 'admin') {
            $agentPermissions = $this->resolvePermissionsWithInheritance('agent-back-office', 
                $allRolePermissions['agent-back-office'], $allRolePermissions);
            
            $finalPermissions = array_merge($agentPermissions, $permissions);
        }

        return array_unique($finalPermissions);
    }

    /**
     * Convertir le nom de rôle slug vers le nom d'affichage
     */
    private function getRoleDisplayName($roleSlug)
    {
        $roleNames = [
            'particulier' => 'Particulier',
            'business' => 'Business',
            'agent' => 'Agent',
            'agent-back-office' => 'Agent Back Office',
            'admin' => 'Admin',
            'super-admin' => 'Super Admin',
        ];

        return $roleNames[$roleSlug] ?? $roleSlug;
    }

    /**
     * Assigner les permissions à un rôle (adaptées table existante role_privileges)
     */
    private function assignPermissionsToRole($roleId, $permissionIds)
    {
        $assignments = [];
        foreach ($permissionIds as $permissionId) {
            $assignments[] = [
                'role_id' => $roleId,
                'privilege_id' => $permissionId, // Utilise 'privilege_id' au lieu de 'permission_id'
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($assignments)) {
            DB::table('role_privileges')->insert($assignments);
        }
    }
}