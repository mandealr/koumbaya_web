<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\UserType;

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
        $customerTypeId = UserType::where('code', 'customer')->first()->id;
        $merchantTypeId = UserType::where('code', 'merchant')->first()->id;
        $adminTypeId = UserType::where('code', 'admin')->first()->id;

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
                'name' => 'Business Enterprise',
                'description' => 'Marchand entreprise avec toutes les fonctionnalités (tickets personnalisables)',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $merchantTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Business Individual',
                'description' => 'Vendeur individuel avec contraintes (500 tickets fixes)',
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

        // Créer les rôles avec firstOrCreate (évite les doublons)
        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'user_type_id' => $roleData['user_type_id']],
                $roleData
            );
        }

        echo "✅ Rôles Koumbaya créés (avec nouveaux profils vendeurs) :\n";
        echo "   - Particulier (pour customers)\n";
        echo "   - Business Enterprise (pour merchants - flexibilité complète)\n";
        echo "   - Business Individual (pour vendeurs - 500 tickets fixes)\n";
        echo "   - Agent (pour admins)\n";
        echo "   - Admin (pour admins)\n";
        echo "   - Super Admin (pour admins)\n";
    }
}