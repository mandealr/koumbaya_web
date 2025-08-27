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
     * Crée les types d'utilisateurs selon le système Koumbaya
     */
    public function run(): void
    {
        $userTypes = [
            [
                'name' => 'Marchand',
                'code' => 'merchant',
                'description' => 'Utilisateur marchand qui peut vendre des produits',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Client',
                'code' => 'customer',
                'description' => 'Client qui achète des produits et participe aux loteries',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administrateur',
                'code' => 'admin',
                'description' => 'Administrateur du système',
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

        echo "✅ Types d'utilisateurs créés :\n";
        echo "   - Marchand (merchant) : Utilisateurs vendeurs\n";
        echo "   - Client (customer) : Utilisateurs acheteurs\n";
        echo "   - Administrateur (admin) : Administrateurs système\n";
    }
}