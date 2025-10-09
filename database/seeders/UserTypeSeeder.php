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
        $userTypes = [
            [
                'name' => 'Administrateur',
                'code' => 'admin',
                'description' => 'Accès à l\'espace d\'administration',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Client/Marchand',
                'code' => 'customer',
                'description' => 'Accès à l\'espace client/marchand (particulier, business_individual, business_enterprise)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Créer les types d'utilisateurs avec firstOrCreate (évite les doublons)
        foreach ($userTypes as $userTypeData) {
            UserType::firstOrCreate(
                ['code' => $userTypeData['code']],
                $userTypeData
            );
        }

        echo "✅ Types d'utilisateurs créés (Niveau 1) :\n";
        echo "   - Administrateur (admin) : Accès espace administration\n";
        echo "   - Client/Marchand (customer) : Accès espace client/marchand\n";
    }
}