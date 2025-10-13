<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * PRODUCTION - Option B : Base avec un super administrateur initial
     *
     * Ordre de seeding :
     * 1. Données de référence (pays, langues, catégories)
     * 2. Système de rôles et privilèges (du plus important au moins important)
     * 3. Super administrateur initial
     * 4. Configuration système
     */
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du seeding Koumbaya PRODUCTION...');
        $this->command->info('');

        $this->call([
            // === ÉTAPE 1: Données de référence ===
            CountrySeeder::class,        // 20 pays africains
            LanguageSeeder::class,       // Français, Anglais
            CategorySeeder::class,       // 22 catégories produits
            UserTypeSeeder::class,       // 2 types : admin (ID: 1), customer (ID: 2)

            // === ÉTAPE 2: Système de sécurité (ordre hiérarchique) ===
            RoleSeeder::class,           // 6 rôles : Super Admin → Particulier
            PermissionSeeder::class,     // 30 privilèges
            RolePermissionSeeder::class, // Associations rôles ↔ privilèges

            // === ÉTAPE 3: Super Administrateur initial ===
            MinimalUserSeeder::class,    // 1 super admin pour gérer la plateforme

            // === ÉTAPE 4: Configuration système ===
            SettingsSeeder::class,       // Paramètres application
            PaymentMethodsSeeder::class, // Méthodes de paiement E-Billing
        ]);

        $this->command->info('');
        $this->command->info('✅ Seeding Koumbaya PRODUCTION terminé avec succès !');
        $this->command->info('');
        $this->command->info('📋 COMPTE SUPER ADMINISTRATEUR :');
        $this->command->info('');
        $this->command->info('   👑 Email: admin@koumbaya.com');
        $this->command->info('   📱 Téléphone: +24177000001');
        $this->command->info('   🔑 Mot de passe: Koumbaya@Admin2024!');
        $this->command->info('');
        $this->command->info('⚠️  IMPORTANT: Changez le mot de passe après la première connexion !');
        $this->command->info('');
        $this->command->info('📊 STRUCTURE CRÉÉE :');
        $this->command->info('   ✅ 2 UserTypes : Admin (1) > Customer (2)');
        $this->command->info('   ✅ 6 Rôles : Super Admin (1) → Particulier (6)');
        $this->command->info('   ✅ 30 Privilèges assignés');
        $this->command->info('   ✅ 20 Pays africains');
        $this->command->info('   ✅ 22 Catégories de produits');
        $this->command->info('   ✅ Configuration E-Billing');
        $this->command->info('');
        $this->command->info('🎯 Base de données prête pour la PRODUCTION !');
    }
}
