<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserType;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\UserWallet;
use App\Models\Country;
use App\Models\Language;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crée les utilisateurs de test selon la structure BD optimisée Koumbaya
     */
    public function run(): void
    {
        // Récupérer les IDs des user_types
        $customerTypeId = UserType::where('code', 'customer')->first()->id;
        $merchantTypeId = UserType::where('code', 'merchant')->first()->id;
        $adminTypeId = UserType::where('code', 'admin')->first()->id;

        // Récupérer l'ID du Gabon
        $gabonId = Country::where('code', 'GA')->first()->id ?? 1;

        // Récupérer l'ID de la langue française
        $frenchId = Language::where('code', 'fr')->first()->id ?? 1;

        $users = [
            // === SUPER ADMINISTRATEUR ===
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'superadmin@koumbaya.ga',
                'phone' => '+24107000001',
                'password' => Hash::make('SuperAdmin2024!'),
                'user_type_id' => $adminTypeId,
                'country_id' => $gabonId,
                'language_id' => $frenchId,
                'is_active' => true,
                'is_phone_verified' => true,
                'is_email_verified' => true,
                'verified_at' => now(),
                'city' => 'Libreville',
                'role_assignment' => 'Super Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === ADMIN PLATEFORME ===
            [
                'first_name' => 'Admin',
                'last_name' => 'Manager',
                'email' => 'admin@koumbaya.ga',
                'phone' => '+24107000002',
                'password' => Hash::make('Admin2024!'),
                'user_type_id' => $adminTypeId,
                'country_id' => $gabonId,
                'language_id' => $frenchId,
                'is_active' => true,
                'is_phone_verified' => true,
                'is_email_verified' => true,
                'verified_at' => now(),
                'city' => 'Libreville',
                'role_assignment' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === AGENT SUPPORT ===
            [
                'first_name' => 'Support',
                'last_name' => 'Agent',
                'email' => 'agent@koumbaya.ga',
                'phone' => '+24107000003',
                'password' => Hash::make('Agent2024!'),
                'user_type_id' => $adminTypeId,
                'country_id' => $gabonId,
                'language_id' => $frenchId,
                'is_active' => true,
                'is_phone_verified' => true,
                'is_email_verified' => true,
                'verified_at' => now(),
                'city' => 'Port-Gentil',
                'role_assignment' => 'Agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === MARCHAND VÉRIFIÉ 1 ===
            [
                'first_name' => 'Marie',
                'last_name' => 'Commerçante',
                'email' => 'merchant1@koumbaya.ga',
                'phone' => '+24107000004',
                'password' => Hash::make('Merchant2024!'),
                'user_type_id' => $merchantTypeId,
                'country_id' => $gabonId,
                'language_id' => $frenchId,
                'is_active' => true,
                'is_phone_verified' => true,
                'is_email_verified' => true,
                'verified_at' => now(),
                'city' => 'Libreville',
                'address' => 'Quartier Nombakélé',
                'company_name' => 'Marie Commerce SARL',
                'company_registration' => 'GA20240001',
                'role_assignment' => 'Business Enterprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === MARCHAND VÉRIFIÉ 2 ===
            [
                'first_name' => 'Paul',
                'last_name' => 'Vendeur',
                'email' => 'merchant2@koumbaya.ga',
                'phone' => '+24107000005',
                'password' => Hash::make('Merchant2024!'),
                'user_type_id' => $merchantTypeId,
                'country_id' => $gabonId,
                'language_id' => $frenchId,
                'is_active' => true,
                'is_phone_verified' => true,
                'is_email_verified' => true,
                'verified_at' => now(),
                'city' => 'Oyem',
                'address' => 'Centre ville',
                'company_name' => 'Paul Electronics',
                'role_assignment' => 'Business Individual',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === CLIENT BASIQUE 1 ===
            [
                'first_name' => 'Jean',
                'last_name' => 'Acheteur',
                'email' => 'client1@koumbaya.ga',
                'phone' => '+24107000006',
                'password' => Hash::make('Client2024!'),
                'user_type_id' => $customerTypeId,
                'country_id' => $gabonId,
                'language_id' => $frenchId,
                'is_active' => true,
                'is_phone_verified' => true,
                'is_email_verified' => true,
                'verified_at' => now(),
                'city' => 'Port-Gentil',
                'address' => 'Cité CECA',
                'role_assignment' => 'Particulier',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === CLIENT BASIQUE 2 ===
            [
                'first_name' => 'Fatou',
                'last_name' => 'Cliente',
                'email' => 'client2@koumbaya.ga',
                'phone' => '+24107000007',
                'password' => Hash::make('Client2024!'),
                'user_type_id' => $customerTypeId,
                'country_id' => $gabonId,
                'language_id' => $frenchId,
                'is_active' => true,
                'is_phone_verified' => true,
                'is_email_verified' => true,
                'verified_at' => now(),
                'city' => 'Franceville',
                'address' => 'Quartier Potos',
                'role_assignment' => 'Particulier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Créer les utilisateurs avec firstOrCreate et assigner les rôles
        foreach ($users as $userData) {
            $roleAssignment = $userData['role_assignment'];
            unset($userData['role_assignment']); // Retirer ce champ pour l'insertion

            // Créer l'utilisateur avec firstOrCreate
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            if ($user->wasRecentlyCreated) {
                echo "✅ Utilisateur créé : " . $user->email . " (" . $roleAssignment . ")\n";
            } else {
                echo "⚠️  Utilisateur existe déjà : " . $user->email . " (mis à jour)\n";
            }

            // Attribution des rôles
            $this->assignUserRole($user->id, $roleAssignment);

            // Créer un portefeuille pour les customers et merchants
            if ($userData['user_type_id'] === $customerTypeId || $userData['user_type_id'] === $merchantTypeId) {
                $this->createUserWallet($user->id, $userData['user_type_id'], $customerTypeId, $merchantTypeId);
            }
        }

        echo "\n🎯 " . count($users) . " utilisateurs de test créés avec la nouvelle structure BD.\n";
    }

    /**
     * Assigner un rôle à un utilisateur
     */
    private function assignUserRole($userId, $roleName)
    {
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            UserRole::firstOrCreate(
                [
                    'user_id' => $userId,
                    'role_id' => $role->id,
                ],
                [
                    'user_id' => $userId,
                    'role_id' => $role->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Créer un portefeuille utilisateur
     */
    private function createUserWallet($userId, $userTypeId, $customerTypeId, $merchantTypeId)
    {
        // Déterminer le solde initial selon le type d'utilisateur
        $initialBalance = match ($userTypeId) {
            $merchantTypeId => 100000, // 100,000 FCFA pour les marchands (démo)
            $customerTypeId => 50000,  // 50,000 FCFA pour les clients
            default => 0
        };

        UserWallet::firstOrCreate(
            ['user_id' => $userId],
            [
                'user_id' => $userId,
                'balance' => $initialBalance,
                'pending_balance' => 0,
                'currency' => 'XAF',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}