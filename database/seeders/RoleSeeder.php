<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crée le système hybride de rôles Koumbaya :
     * - Particulier : rôle de base obligatoire
     * - Business : rôle additionnel (+ particulier)
     * - Managers : rôles administratifs
     */
    public function run(): void
    {
        // Créer les rôles selon le système hybride Koumbaya (adaptés à la table 'roles' existante)
        $roles = [
            // === RÔLES CUSTOMERS (user_type_id = 2) ===
            
            // RÔLE DE BASE (obligatoire pour customers)
            [
                'name' => 'Particulier',
                'description' => 'Rôle de base pour tous les clients - Mode acheteur',
                'active' => true,
                'mutable' => false, // Rôle système non modifiable
                'user_type_id' => 2, // CUSTOMER
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // RÔLE BUSINESS (additionnel)
            [
                'name' => 'Business',
                'description' => 'Rôle vendeur - Toujours combiné avec particulier',
                'active' => true,
                'mutable' => false, // Rôle système non modifiable
                'user_type_id' => 2, // CUSTOMER
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === RÔLES MANAGERS (user_type_id = 1) ===
            [
                'name' => 'Agent',
                'description' => 'Agent de support client et modération basique',
                'active' => true,
                'mutable' => false, // Rôle système non modifiable
                'user_type_id' => 1, // MANAGER
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Agent Back Office',
                'description' => 'Agent back office - Gestion avancée et reporting',
                'active' => true,
                'mutable' => false, // Rôle système non modifiable
                'user_type_id' => 1, // MANAGER
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin',
                'description' => 'Administrateur - Gestion complète de la plateforme',
                'active' => true,
                'mutable' => false, // Rôle système non modifiable
                'user_type_id' => 1, // MANAGER
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Super Admin',
                'description' => 'Super administrateur - Accès système complet',
                'active' => true,
                'mutable' => false, // Rôle système non modifiable
                'user_type_id' => 1, // MANAGER
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insérer les rôles dans la table 'roles' existante
        DB::table('roles')->insert($roles);

        echo "✅ Système hybride de rôles Koumbaya créé :\n";
        echo "   - Particulier (base obligatoire)\n";
        echo "   - Business (+ particulier)\n";
        echo "   - Managers : Agent, Agent BO, Admin, Super Admin\n";
    }
}