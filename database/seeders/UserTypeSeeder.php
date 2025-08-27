<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        // Insérer les types d'utilisateurs
        DB::table('user_types')->insert($userTypes);

        echo "✅ Types d'utilisateurs créés :\n";
        echo "   - Marchand (merchant) : Utilisateurs vendeurs\n";
        echo "   - Client (customer) : Utilisateurs acheteurs\n";
        echo "   - Administrateur (admin) : Administrateurs système\n";
    }
}