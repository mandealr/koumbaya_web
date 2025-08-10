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
                'id' => 1,
                'name' => 'MANAGER',
                'description' => 'Utilisateurs de gestion (Super Admin, Admin, Agent, Agent Back Office)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2, 
                'name' => 'CUSTOMER',
                'description' => 'Utilisateurs clients (Particulier, Business/Marchands)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insérer les types d'utilisateurs
        DB::table('user_types')->insert($userTypes);

        echo "✅ Types d'utilisateurs créés :\n";
        echo "   - MANAGER (ID: 1) : Super Admin, Admin, Agent, Agent BO\n";
        echo "   - CUSTOMER (ID: 2) : Particulier, Business\n";
    }
}