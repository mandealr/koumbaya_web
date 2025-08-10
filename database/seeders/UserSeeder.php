<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crée les utilisateurs de test selon le système hybride Koumbaya
     */
    public function run(): void
    {
        // Récupérer l'ID du Gabon
        $gabonId = DB::table('countries')->where('iso_code_2', 'GA')->first()->id ?? 1;

        $users = [
            // === SUPER ADMIN ===
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'superadmin@koumbaya.ga',
                'phone' => '+24107000001',
                'password' => Hash::make('SuperAdmin2024!'),
                'user_type_id' => 1, // MANAGER
                // Les managers n'ont pas d'account_type dans ce schéma
                // 'account_type' => 'manager',
                // 'email_verified_at' => now(), // Colonne supprimée par migration
                // 'phone_verified_at' => now(), // Colonne n'existe pas
                'is_active' => true,
                'country_id' => $gabonId,
                'city' => 'Libreville',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === ADMIN ===
            [
                'first_name' => 'Admin',
                'last_name' => 'Manager',
                'email' => 'admin@koumbaya.ga',
                'phone' => '+24107000002',
                'password' => Hash::make('Admin2024!'),
                'user_type_id' => 1, // MANAGER
                // Les managers n'ont pas d'account_type dans ce schéma
                // 'account_type' => 'manager',
                // 'email_verified_at' => now(), // Colonne supprimée par migration
                // 'phone_verified_at' => now(), // Colonne n'existe pas
                'is_active' => true,
                'country_id' => $gabonId,
                'city' => 'Libreville',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === AGENT ===
            [
                'first_name' => 'Support',
                'last_name' => 'Agent',
                'email' => 'agent@koumbaya.ga',
                'phone' => '+24107000003',
                'password' => Hash::make('Agent2024!'),
                'user_type_id' => 1, // MANAGER
                // Les managers n'ont pas d'account_type dans ce schéma
                // 'account_type' => 'manager',
                // 'email_verified_at' => now(), // Colonne supprimée par migration
                // 'phone_verified_at' => now(), // Colonne n'existe pas
                'is_active' => true,
                'country_id' => $gabonId,
                'city' => 'Port-Gentil',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === AGENT BACK OFFICE ===
            [
                'first_name' => 'Back Office',
                'last_name' => 'Manager',
                'email' => 'backoffice@koumbaya.ga',
                'phone' => '+24107000004',
                'password' => Hash::make('BackOffice2024!'),
                'user_type_id' => 1, // MANAGER
                // Les managers n'ont pas d'account_type dans ce schéma
                // 'account_type' => 'manager',
                // 'email_verified_at' => now(), // Colonne supprimée par migration
                // 'phone_verified_at' => now(), // Colonne n'existe pas
                'is_active' => true,
                'country_id' => $gabonId,
                'city' => 'Libreville',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === BUSINESS (Particulier + Business) ===
            [
                'first_name' => 'Marie',
                'last_name' => 'Entrepreneuriat',
                'email' => 'business@koumbaya.ga',
                'phone' => '+24107000005',
                'password' => Hash::make('Business2024!'),
                'user_type_id' => 2, // CUSTOMER
                'account_type' => 'business',
                // 'current_mode' => 'seller', // Mode par défaut (colonne n'existe pas)
                // 'date_of_birth' => '1985-03-15', // Colonne n'existe pas
                // 'email_verified_at' => now(), // Colonne supprimée par migration
                // 'phone_verified_at' => now(), // Colonne n'existe pas
                'is_active' => true,
                'country_id' => $gabonId,
                'city' => 'Libreville',
                'address' => 'Quartier Nombakélé',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === PARTICULIER UNIQUEMENT ===
            [
                'first_name' => 'Jean',
                'last_name' => 'Acheteur',
                'email' => 'particulier1@koumbaya.ga',
                'phone' => '+24107000006',
                'password' => Hash::make('Particulier2024!'),
                'user_type_id' => 2, // CUSTOMER
                'account_type' => 'personal',
                // 'current_mode' => 'buyer', // Colonne n'existe pas
                // 'date_of_birth' => '1990-07-22', // Colonne n'existe pas
                // 'email_verified_at' => now(), // Colonne supprimée par migration
                // 'phone_verified_at' => now(), // Colonne n'existe pas
                'is_active' => true,
                'country_id' => $gabonId,
                'city' => 'Port-Gentil',
                'address' => 'Cité CECA',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === PARTICULIER UNIQUEMENT 2 ===
            [
                'first_name' => 'Fatou',
                'last_name' => 'Cliente',
                'email' => 'particulier2@koumbaya.ga',
                'phone' => '+24107000007',
                'password' => Hash::make('Particulier2024!'),
                'user_type_id' => 2, // CUSTOMER
                'account_type' => 'personal',
                // 'current_mode' => 'buyer', // Colonne n'existe pas
                // 'date_of_birth' => '1992-11-08', // Colonne n'existe pas
                // 'email_verified_at' => now(), // Colonne supprimée par migration
                // 'phone_verified_at' => now(), // Colonne n'existe pas
                'is_active' => true,
                'country_id' => $gabonId,
                'city' => 'Franceville',
                'address' => 'Quartier Potos',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === BUSINESS 2 (Démo supplémentaire) ===
            [
                'first_name' => 'Paul',
                'last_name' => 'Commerce',
                'email' => 'business2@koumbaya.ga',
                'phone' => '+24107000008',
                'password' => Hash::make('Business2024!'),
                'user_type_id' => 2, // CUSTOMER
                'account_type' => 'business',
                // 'current_mode' => 'buyer', // Démarré en mode acheteur (colonne n'existe pas)
                // 'date_of_birth' => '1988-12-03', // Colonne n'existe pas
                // 'email_verified_at' => now(), // Colonne supprimée par migration
                // 'phone_verified_at' => now(), // Colonne n'existe pas
                'is_active' => true,
                'country_id' => $gabonId,
                'city' => 'Oyem',
                'address' => 'Centre ville',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insérer les utilisateurs et assigner les rôles
        foreach ($users as $userData) {
            $userId = DB::table('users')->insertGetId($userData);

            // Attribution des rôles selon le système hybride
            $this->assignUserRoles($userId, $userData);

            // Créer un portefeuille pour les customers
            if (isset($userData['account_type']) && in_array($userData['account_type'], ['personal', 'business'])) {
                $this->createUserWallet($userId, $userData);
            }

            // Créer profil business si nécessaire (désactivé - table non disponible)
            // if ($userData['account_type'] === 'business') {
            //     $this->createBusinessProfile($userId, $userData);
            // }

            $accountInfo = isset($userData['account_type']) ? $userData['account_type'] : 'manager';
            echo "✅ Utilisateur créé : " . $userData['email'] . " (" . $accountInfo . ")\n";
        }

        echo "\n🎯 " . count($users) . " utilisateurs de test créés selon le système hybride Koumbaya.\n";
    }

    /**
     * Assigner les rôles selon le système hybride
     */
    private function assignUserRoles($userId, $userData)
    {
        $rolesToAssign = [];

        // Déterminer les rôles selon le type de compte ou l'email (pour les managers)
        if (isset($userData['account_type'])) {
            switch ($userData['account_type']) {
                case 'business':
                    // Business = Particulier + Business (règle hybride)
                    $rolesToAssign = ['particulier', 'business'];
                    break;

                case 'personal':
                    // Personnel = Particulier uniquement
                    $rolesToAssign[] = 'particulier';
                    break;
            }
        } else {
            // Pour les managers (pas d'account_type), utiliser l'email
            $managerRole = $this->getManagerRole($userData['email']);
            if ($managerRole) {
                $rolesToAssign[] = $managerRole;
            }
        }

        // Assigner les rôles (adapter aux tables existantes)
        foreach ($rolesToAssign as $roleName) {
            $role = DB::table('roles')->where('name', $this->getRoleDisplayName($roleName))->first();

            if ($role) {
                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $role->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Convertir le nom de rôle slug vers le nom d'affichage
     */
    private function getRoleDisplayName($roleSlug)
    {
        $roleNames = [
            'particulier' => 'Particulier',
            'business' => 'Business',
            'agent' => 'Agent',
            'agent-back-office' => 'Agent Back Office',
            'admin' => 'Admin',
            'super-admin' => 'Super Admin',
        ];

        return $roleNames[$roleSlug] ?? $roleSlug;
    }

    /**
     * Déterminer le rôle manager selon l'email
     */
    private function getManagerRole($email)
    {
        $managerRoles = [
            'superadmin@koumbaya.ga' => 'super-admin',
            'admin@koumbaya.ga' => 'admin',
            'agent@koumbaya.ga' => 'agent',
            'backoffice@koumbaya.ga' => 'agent-back-office',
        ];

        return $managerRoles[$email] ?? null;
    }

    /**
     * Créer un portefeuille utilisateur
     */
    private function createUserWallet($userId, $userData)
    {
        $initialBalance = match ($userData['account_type']) {
            'business' => 25000,  // 25,000 FCFA pour les business (démo)
            'personal' => 50000,  // 50,000 FCFA pour les particuliers
            default => 0
        };

        DB::table('user_wallets')->insert([
            'user_id' => $userId,
            'balance' => $initialBalance,
            'pending_balance' => 0,
            'currency' => 'XAF',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Créer un profil business
     */
    private function createBusinessProfile($userId, $userData)
    {
        $businessNames = [
            'business@koumbaya.ga' => 'Marie Entrepreneuriat SARL',
            'business2@koumbaya.ga' => 'Commerce Paul & Fils',
        ];

        $businessName = $businessNames[$userData['email']] ??
            $userData['first_name'] . ' ' . $userData['last_name'] . ' Business';

        DB::table('business_profiles')->insert([
            'user_id' => $userId,
            'business_name' => $businessName,
            'business_type' => 'retail',
            'business_email' => $userData['email'],
            'business_phone' => $userData['phone'],
            'business_address' => $userData['address'] ?? $userData['city'],
            'description' => 'Entreprise spécialisée dans la vente de produits de qualité via tombolas.',
            'verification_status' => 'verified',
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
