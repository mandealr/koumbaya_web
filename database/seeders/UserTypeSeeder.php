<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserType;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Architecture à deux niveaux :
     * Niveau 1 : UserType (admin, customer)
     * Niveau 2 : Roles (rattachés aux user types)
     */
    public function run(): void
    {
        echo "🔄 Nettoyage des types d'utilisateurs existants...\n";

        // Vider la table user_types
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        UserType::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "📝 Création des nouveaux types d'utilisateurs...\n";

        // Ordre : du plus important au moins important
        $userTypes = [
            // === ADMIN : Plus important ===
            [
                'id' => 1,
                'name' => 'Administrateur',
                'code' => 'admin',
                'description' => 'Accès à l\'espace d\'administration (superadmin, admin, agent)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // === CUSTOMER : Moins important ===
            [
                'id' => 2,
                'name' => 'Client/Marchand',
                'code' => 'customer',
                'description' => 'Accès à l\'espace client/marchand (particulier, business_individual, business_enterprise)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($userTypes as $userTypeData) {
            UserType::create($userTypeData);
        }

        echo "✅ Types d'utilisateurs créés (Niveau 1) :\n";
        echo "   - ID 1 : Administrateur (admin) → Rôles: superadmin, admin, agent\n";
        echo "   - ID 2 : Client/Marchand (customer) → Rôles: particulier, business_individual, business_enterprise\n";
    }
}