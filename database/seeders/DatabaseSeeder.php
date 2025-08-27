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
     * Ordre de seeding pour la structure BD optimisée Koumbaya :
     * 1. Données de base (pays, langues, catégories)
     * 2. Système de rôles et privilèges
     * 3. Utilisateurs avec attribution de rôles
     * 4. Configuration et données métier
     */
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du seeding Koumbaya BD optimisée...');

        $this->call([
            // === ÉTAPE 1: Données de référence ===
            CountrySeeder::class,     // Déjà dans migration
            LanguageSeeder::class,    // Déjà dans migration
            CategorySeeder::class,
            UserTypeSeeder::class,       // Types : customer, merchant, admin

            // === ÉTAPE 2: Système de sécurité ===
            RoleSeeder::class,           // Rôles optimisés pour chaque user_type
            PermissionSeeder::class,     // Privilèges dans table privileges
            RolePermissionSeeder::class, // Associations roles ↔ privileges

            // === ÉTAPE 3: Utilisateurs et profils ===
            UserSeeder::class,           // Utilisateurs avec nouvelle structure

            // === ÉTAPE 4: Configuration système ===
            SettingsSeeder::class,       // Paramètres application
            PaymentMethodsSeeder::class, // Méthodes de paiement E-Billing

            // === ÉTAPE 5: Données métier ===
            ProductSeeder::class,        // Produits pour démonstration
            LotterySeeder::class,        // Tombolas de test
            // UserRatingSeeder::class,     // Évaluations utilisateurs (désactivé)
        ]);

        $this->command->info('✅ Seeding Koumbaya BD optimisée terminé avec succès !');
        $this->command->info('');
        $this->command->info('📋 COMPTES DE TEST CRÉÉS :');
        $this->command->info('');
        $this->command->info('🛡️  ADMINISTRATEURS:');
        $this->command->info('   👑 Super Admin: superadmin@koumbaya.ga (SuperAdmin2024!)');
        $this->command->info('   🔧 Admin Plateforme: admin@koumbaya.ga (Admin2024!)');
        $this->command->info('   👨‍💼 Agent Support: agent@koumbaya.ga (Agent2024!)');
        $this->command->info('');
        $this->command->info('🏪 COMPTES MARCHANDS:');
        $this->command->info('   🏢 Marchand 1: merchant1@koumbaya.ga (Merchant2024!)');
        $this->command->info('   🏢 Marchand 2: merchant2@koumbaya.ga (Merchant2024!)');
        $this->command->info('');
        $this->command->info('👤 COMPTES CLIENTS:');
        $this->command->info('   🛒 Client 1: client1@koumbaya.ga (Client2024!)');
        $this->command->info('   🛒 Client 2: client2@koumbaya.ga (Client2024!)');
        $this->command->info('');
        $this->command->info('💰 Portefeuilles initialisés avec soldes de test');
        $this->command->info('🔐 Système de rôles et privilèges complet');
        $this->command->info('📊 Architecture order-centric prête pour E-Billing');
    }
}
