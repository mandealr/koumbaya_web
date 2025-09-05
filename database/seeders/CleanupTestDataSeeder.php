<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanupTestDataSeeder extends Seeder
{
    /**
     * Nettoie toutes les données de test (produits, commandes, paiements, tickets)
     * en préservant les utilisateurs et la structure de base
     */
    public function run(): void
    {
        $this->command->info('🧹 Démarrage du nettoyage des données de test...');

        // Désactiver les contraintes de clés étrangères temporairement
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // 1. Nettoyer les données liées aux commandes (dans l'ordre de dépendance)
            $this->cleanOrderRelatedData();
            
            // 2. Nettoyer les données liées aux produits/tombolas
            $this->cleanProductRelatedData();
            
            // 3. Nettoyer les données financières
            $this->cleanFinancialData();
            
            // 4. Nettoyer les notifications liées aux produits/commandes
            $this->cleanNotifications();
            
            // 5. Afficher le résumé
            $this->showCleanupSummary();
            
        } catch (\Exception $e) {
            $this->command->error('Erreur lors du nettoyage : ' . $e->getMessage());
            throw $e;
        } finally {
            // Réactiver les contraintes de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->command->info('✅ Nettoyage terminé avec succès !');
        $this->command->info('');
        $this->command->info('📋 DONNÉES PRÉSERVÉES :');
        $this->command->info('   - Utilisateurs et leurs rôles');
        $this->command->info('   - Structure de base (pays, langues, catégories, etc.)');
        $this->command->info('   - Configuration système');
        $this->command->info('');
        $this->command->info('🗑️ DONNÉES SUPPRIMÉES :');
        $this->command->info('   - Tous les produits et tombolas');
        $this->command->info('   - Toutes les commandes et leur suivi');
        $this->command->info('   - Tous les paiements et transactions');
        $this->command->info('   - Tous les tickets de tombola');
        $this->command->info('   - Tous les remboursements');
        $this->command->info('   - Notifications liées aux produits/commandes');
        $this->command->info('');
        $this->command->info('🚀 Prêt pour de nouveaux tests avec les nouvelles règles de tombola !');
    }

    /**
     * Nettoie les données liées aux commandes
     */
    private function cleanOrderRelatedData(): void
    {
        $this->command->info('🗑️ Suppression des données liées aux commandes...');
        
        // Supprimer les tickets de tombola (dépendent des commandes)
        $ticketsCount = DB::table('lottery_tickets')->count();
        DB::table('lottery_tickets')->truncate();
        $this->command->info("   - {$ticketsCount} tickets de tombola supprimés");
        
        // Supprimer les commandes
        $ordersCount = DB::table('orders')->count();
        DB::table('orders')->truncate();
        $this->command->info("   - {$ordersCount} commandes supprimées");
    }

    /**
     * Nettoie les données liées aux produits et tombolas
     */
    private function cleanProductRelatedData(): void
    {
        $this->command->info('🗑️ Suppression des produits et tombolas...');
        
        // Supprimer les tombolas
        $lotteriesCount = DB::table('lotteries')->count();
        DB::table('lotteries')->truncate();
        $this->command->info("   - {$lotteriesCount} tombolas supprimées");
        
        // Supprimer les historiques de tirages
        $drawHistoriesCount = DB::table('draw_histories')->count();
        DB::table('draw_histories')->truncate();
        $this->command->info("   - {$drawHistoriesCount} historiques de tirages supprimés");
        
        // Supprimer les produits
        $productsCount = DB::table('products')->count();
        DB::table('products')->truncate();
        $this->command->info("   - {$productsCount} produits supprimés");
    }

    /**
     * Nettoie les données financières
     */
    private function cleanFinancialData(): void
    {
        $this->command->info('🗑️ Suppression des données financières...');
        
        // Supprimer les paiements
        $paymentsCount = DB::table('payments')->count();
        DB::table('payments')->truncate();
        $this->command->info("   - {$paymentsCount} paiements supprimés");
        
        // Supprimer les remboursements
        $refundsCount = DB::table('refunds')->count();
        DB::table('refunds')->truncate();
        $this->command->info("   - {$refundsCount} remboursements supprimés");
        
        // Réinitialiser les portefeuilles (garder les portefeuilles mais remettre solde à zéro)
        $walletsUpdated = DB::table('user_wallets')->update([
            'balance' => 0.00,
            'updated_at' => now()
        ]);
        $this->command->info("   - {$walletsUpdated} portefeuilles réinitialisés");
    }

    /**
     * Nettoie les notifications liées aux produits/commandes
     */
    private function cleanNotifications(): void
    {
        $this->command->info('🗑️ Suppression des notifications liées...');
        
        // Supprimer les notifications liées aux commandes, produits, paiements
        $notificationsCount = DB::table('notifications')
            ->whereIn('type', [
                'order_created',
                'order_paid', 
                'order_delivered',
                'payment_received',
                'payment_failed',
                'lottery_won',
                'lottery_ended',
                'product_sold',
                'refund_processed'
            ])
            ->count();
            
        DB::table('notifications')
            ->whereIn('type', [
                'order_created',
                'order_paid', 
                'order_delivered',
                'payment_received',
                'payment_failed',
                'lottery_won',
                'lottery_ended',
                'product_sold',
                'refund_processed'
            ])
            ->delete();
            
        $this->command->info("   - {$notificationsCount} notifications supprimées");
    }

    /**
     * Affiche un résumé des données restantes
     */
    private function showCleanupSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 RÉSUMÉ POST-NETTOYAGE :');
        
        // Compter les données restantes importantes
        $usersCount = DB::table('users')->count();
        $rolesCount = DB::table('roles')->count();
        $categoriesCount = DB::table('categories')->count();
        $countriesCount = DB::table('countries')->count();
        $walletsCount = DB::table('user_wallets')->count();
        $remainingNotifications = DB::table('notifications')->count();
        
        $this->command->info("   - Utilisateurs préservés: {$usersCount}");
        $this->command->info("   - Rôles préservés: {$rolesCount}");
        $this->command->info("   - Catégories préservées: {$categoriesCount}");  
        $this->command->info("   - Pays préservés: {$countriesCount}");
        $this->command->info("   - Portefeuilles réinitialisés: {$walletsCount}");
        $this->command->info("   - Notifications restantes: {$remainingNotifications}");
        
        // Vérifier que les tables principales sont vides
        $productsLeft = DB::table('products')->count();
        $ordersLeft = DB::table('orders')->count();
        $paymentsLeft = DB::table('payments')->count();
        $ticketsLeft = DB::table('lottery_tickets')->count();
        
        if ($productsLeft === 0 && $ordersLeft === 0 && $paymentsLeft === 0 && $ticketsLeft === 0) {
            $this->command->info('✅ Toutes les données de test ont été correctement supprimées');
        } else {
            $this->command->warn("⚠️  Données restantes: Produits({$productsLeft}), Commandes({$ordersLeft}), Paiements({$paymentsLeft}), Tickets({$ticketsLeft})");
        }
    }
}