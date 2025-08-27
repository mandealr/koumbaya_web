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
        // Récupérer les user_type_id créés
        $customerTypeId = DB::table('user_types')->where('code', 'customer')->first()->id;
        $merchantTypeId = DB::table('user_types')->where('code', 'merchant')->first()->id;
        $adminTypeId = DB::table('user_types')->where('code', 'admin')->first()->id;

        $roles = [
            // === RÔLES CLIENT ===
            [
                'name' => 'Particulier',
                'description' => 'Client qui peut acheter des produits et participer aux loteries',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === RÔLES MARCHAND ===
            [
                'name' => 'Business',
                'description' => 'Marchand qui peut vendre des produits et créer des loteries',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $merchantTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === RÔLES ADMIN ===
            [
                'name' => 'Agent',
                'description' => 'Agent de support client et modération basique',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin',
                'description' => 'Administrateur - Gestion complète de la plateforme',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Super Admin',
                'description' => 'Super administrateur - Accès système complet',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insérer les rôles dans la table 'roles' existante
        DB::table('roles')->insert($roles);

        echo "✅ Rôles Koumbaya créés (compatibles code existant) :\n";
        echo "   - Particulier (pour customers)\n";
        echo "   - Business (pour merchants)\n";
        echo "   - Agent (pour admins)\n";
        echo "   - Admin (pour admins)\n";
        echo "   - Super Admin (pour admins)\n";
    }
}