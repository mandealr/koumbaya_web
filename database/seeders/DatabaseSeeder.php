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
     * Ordre de seeding pour le système hybride Koumbaya :
     * 1. Données de base (pays, langues, catégories)
     * 2. Système de rôles et permissions
     * 3. Utilisateurs avec attribution de rôles
     * 4. Données métier (produits, tombolas)
     */
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du seeding Koumbaya...');
        
        $this->call([
            // === ÉTAPE 1: Données de référence ===
            CountrySeeder::class,
            LanguageSeeder::class,
            CategorySeeder::class,
            UserTypeSeeder::class,       // Types d'utilisateurs (MANAGER, CUSTOMER)
            
            // === ÉTAPE 2: Système de sécurité ===
            RoleSeeder::class,           // Rôles hybrides (particulier, business, managers)
            PermissionSeeder::class,     // Permissions atomiques par ressource
            RolePermissionSeeder::class, // Associations rôles-permissions avec héritage
            
            // === ÉTAPE 3: Utilisateurs et profils ===
            UserSeeder::class,           // Utilisateurs de test avec rôles hybrides
            // AuthenticationSeeder::class, // Désactivé - tables non disponibles
            
            // === ÉTAPE 4: Données métier ===
            ProductSeeder::class,        // Produits pour démonstration
            LotterySeeder::class,        // Tombolas de test
            UserRatingSeeder::class,     // Évaluations utilisateurs
        ]);

        $this->command->info('✅ Seeding Koumbaya terminé avec succès !');
        $this->command->info('');
        $this->command->info('📋 COMPTES DE TEST CRÉÉS :');
        $this->command->info('');
        $this->command->info('🛡️  MANAGERS:');
        $this->command->info('   👑 Super Admin: superadmin@koumbaya.ga (SuperAdmin2024!)');
        $this->command->info('   🔧 Admin: admin@koumbaya.ga (Admin2024!)');
        $this->command->info('   👨‍💼 Agent: agent@koumbaya.ga (Agent2024!)');
        $this->command->info('   🏢 Back Office: backoffice@koumbaya.ga (BackOffice2024!)');
        $this->command->info('');
        $this->command->info('💼 COMPTES BUSINESS (Particulier + Vendeur):');
        $this->command->info('   🏪 Business 1: business@koumbaya.ga (Business2024!)');
        $this->command->info('   🏪 Business 2: business2@koumbaya.ga (Business2024!)');
        $this->command->info('');
        $this->command->info('👤 COMPTES PARTICULIER (Acheteur uniquement):');
        $this->command->info('   🛒 Particulier 1: particulier1@koumbaya.ga (Particulier2024!)');
        $this->command->info('   🛒 Particulier 2: particulier2@koumbaya.ga (Particulier2024!)');
        $this->command->info('');
        $this->command->info('🔄 Les comptes BUSINESS peuvent basculer entre mode acheteur/vendeur');
        $this->command->info('🔐 2FA requis pour tous les customers (particulier/business)');
        $this->command->info('📄 PI obligatoire pour customers, optionnel pour managers');
    }
}
