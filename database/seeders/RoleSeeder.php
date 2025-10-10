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
        echo "🔄 Nettoyage des rôles existants...\n";

        // Vider la table roles et user_roles (pivot)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('user_roles')->truncate();
        Role::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "📝 Création des nouveaux rôles...\n";

        // Les IDs sont fixes car on a recréé les user_types avec des IDs fixes
        $adminTypeId = 1;  // admin
        $customerTypeId = 2;  // customer

        $roles = [
            // === RÔLES CUSTOMER (Niveau 2) ===
            [
                'id' => 1,
                'name' => 'particulier',
                'description' => 'Client qui peut acheter des articles et participer aux tirages spéciaux',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'business_individual',
                'description' => 'Vendeur individuel avec contraintes (500 tickets fixes, prix min 100k)',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $customerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
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
                'id' => 4,
                'name' => 'agent',
                'description' => 'Agent de support client et modération basique',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'admin',
                'description' => 'Administrateur - Gestion complète de la plateforme',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'superadmin',
                'description' => 'Super administrateur - Accès système complet et gestion des rôles',
                'active' => true,
                'mutable' => false,
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        echo "✅ Rôles créés (Niveau 2) :\n";
        echo "   CUSTOMER TYPE (ID: 2):\n";
        echo "   - ID 1 : particulier → Client acheteur\n";
        echo "   - ID 2 : business_individual → Vendeur individuel (contraintes)\n";
        echo "   - ID 3 : business_enterprise → Vendeur professionnel\n";
        echo "   ADMIN TYPE (ID: 1):\n";
        echo "   - ID 4 : agent → Agent support\n";
        echo "   - ID 5 : admin → Administrateur\n";
        echo "   - ID 6 : superadmin → Super administrateur\n";
    }
}