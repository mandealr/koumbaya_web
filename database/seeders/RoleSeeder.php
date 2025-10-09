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
     * Architecture à deux niveaux :
     * Niveau 1 : UserType (admin, customer)
     * Niveau 2 : Roles rattachés aux types
     *
     * Pour admin : superadmin, admin, agent
     * Pour customer : particulier, business_individual, business_enterprise
     */
    public function run(): void
    {
        // Récupérer les user_type_id créés
        $customerTypeId = UserType::where('code', 'customer')->first()->id;
        $adminTypeId = UserType::where('code', 'admin')->first()->id;

        $roles = [
            // === RÔLES CUSTOMER (Niveau 2) ===
            [
                'name' => 'particulier',
                'description' => 'Client qui peut acheter des articles et participer aux tirages spéciaux',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'business_individual',
                'description' => 'Vendeur individuel avec contraintes (500 tickets fixes, prix min 100k)',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'business_enterprise',
                'description' => 'Marchand entreprise professionnel avec toutes les fonctionnalités',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === RÔLES ADMIN (Niveau 2) ===
            [
                'name' => 'agent',
                'description' => 'Agent de support client et modération basique',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'admin',
                'description' => 'Administrateur - Gestion complète de la plateforme',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'superadmin',
                'description' => 'Super administrateur - Accès système complet et gestion des rôles',
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

        echo "✅ Rôles créés (Niveau 2) :\n";
        echo "   CUSTOMER TYPE:\n";
        echo "   - particulier : Client acheteur\n";
        echo "   - business_individual : Vendeur individuel (contraintes)\n";
        echo "   - business_enterprise : Vendeur professionnel\n";
        echo "   ADMIN TYPE:\n";
        echo "   - agent : Agent support\n";
        echo "   - admin : Administrateur\n";
        echo "   - superadmin : Super administrateur\n";
    }
}