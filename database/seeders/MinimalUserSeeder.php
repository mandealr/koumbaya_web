<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserType;
use App\Models\Role;
use App\Models\Country;
use App\Models\Language;

class MinimalUserSeeder extends Seeder
{
    /**
     * Créer un seul super administrateur pour la production
     *
     * ⚠️ IMPORTANT: Changer les credentials avant de déployer en production !
     */
    public function run(): void
    {
        echo "👑 Création du Super Administrateur initial...\n";

        $adminTypeId = UserType::where('code', 'admin')->first()->id;
        $gabonId = Country::where('code', 'GA')->first()->id ?? 1;
        $frenchId = Language::where('code', 'fr')->first()->id ?? 1;

        // ⚠️ CHANGER CES CREDENTIALS EN PRODUCTION
        $admin = User::firstOrCreate(
            ['email' => 'admin@koumbaya.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Koumbaya',
                'email' => 'admin@koumbaya.com',
                'phone' => '+24177000001',
                'password' => Hash::make('Koumbaya@Admin2024!'),
                'user_type_id' => $adminTypeId,
                'country_id' => $gabonId,
                'language_id' => $frenchId,
                'is_active' => true,
                'is_phone_verified' => true,
                'is_email_verified' => true,
                'verified_at' => now(),
                'city' => 'Libreville',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            echo "✅ Super Admin créé avec succès\n";
        } else {
            echo "⚠️  Super Admin existe déjà\n";
        }

        // Assigner le rôle Super Admin
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        if ($superAdminRole) {
            $roleExists = \DB::table('user_roles')
                ->where('user_id', $admin->id)
                ->where('role_id', $superAdminRole->id)
                ->exists();

            if (!$roleExists) {
                \DB::table('user_roles')->insert([
                    'user_id' => $admin->id,
                    'role_id' => $superAdminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                echo "✅ Rôle 'Super Admin' assigné\n";
            } else {
                echo "ℹ️  Rôle 'Super Admin' déjà assigné\n";
            }
        } else {
            echo "❌ Erreur: Rôle 'Super Admin' non trouvé!\n";
        }

        echo "\n";
        echo "📋 COMPTE SUPER ADMINISTRATEUR:\n";
        echo "   Email: {$admin->email}\n";
        echo "   Téléphone: {$admin->phone}\n";
        echo "   Mot de passe: Koumbaya@Admin2024!\n";
        echo "   ⚠️  CHANGER LE MOT DE PASSE APRÈS LA PREMIÈRE CONNEXION !\n";
    }
}
