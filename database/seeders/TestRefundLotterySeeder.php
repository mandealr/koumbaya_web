<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Lottery;
use App\Models\Payment;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestRefundLotterySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎯 Creating test lottery for refund testing...');

        // 1. Créer ou récupérer un marchand
        $merchant = User::firstOrCreate(
            ['email' => 'merchant.test@koumbaya.com'],
            [
                'first_name' => 'Marchand',
                'last_name' => 'Test',
                'phone' => '074123456',
                'password' => Hash::make('password'),
                'account_type' => 'merchant',
                'can_sell' => true,
                'can_buy' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("✅ Merchant created/found: {$merchant->email}");

        // 2. Créer une catégorie si nécessaire
        $category = Category::firstOrCreate(
            ['name' => 'Test Category'],
            [
                'description' => 'Category for testing refunds',
                'slug' => 'test-category',
                'is_active' => true,
            ]
        );

        // 3. Créer un produit pour la tombola
        $product = Product::create([
            'merchant_id' => $merchant->id,
            'category_id' => $category->id,
            'name' => 'iPhone 15 Pro Max - Test Refund',
            'title' => 'iPhone 15 Pro Max 256GB - Test de Remboursement',
            'description' => 'Produit test pour vérifier le système de remboursement automatique.',
            'short_description' => 'iPhone 15 Pro Max pour test de remboursement',
            'price' => 850000, // 850,000 FCFA
            'currency' => 'XAF',
            'quantity' => 1,
            'status' => 'active',
            'is_featured' => true,
            'meta' => json_encode([
                'min_participants' => 500, // Nécessite 500 participants
                'max_participants' => 1000,
                'auto_draw' => true,
            ]),
            'images' => json_encode([
                'https://example.com/iphone15.jpg'
            ]),
            'tags' => json_encode(['smartphone', 'apple', 'test']),
        ]);

        $this->command->info("✅ Product created: {$product->name} (Min participants: 500)");

        // 4. Créer une tombola qui expire dans 2 jours (mais sera considérée comme expirée pour le test)
        $expiryDate = Carbon::now()->subDays(2); // Expirée depuis 2 jours
        
        $lottery = Lottery::create([
            'lottery_number' => 'TEST-REFUND-' . time(),
            'product_id' => $product->id,
            'merchant_id' => $merchant->id,
            'ticket_price' => 1700, // 1,700 FCFA par ticket
            'max_tickets' => 500,
            'min_participants' => 500,
            'draw_date' => $expiryDate,
            'end_date' => $expiryDate,
            'start_date' => Carbon::now()->subDays(30),
            'status' => 'active', // Toujours active mais expirée
            'description' => 'Tombola de test pour le système de remboursement - Participants insuffisants',
            'rules' => 'Tombola de test uniquement',
            'sold_tickets' => 100, // Seulement 100 tickets vendus sur 500 requis
        ]);

        $this->command->info("✅ Lottery created: {$lottery->lottery_number}");
        $this->command->info("   - Expired: {$expiryDate->format('Y-m-d H:i:s')}");
        $this->command->info("   - Participants: 100/500 (insufficient)");

        // 5. Créer des utilisateurs acheteurs et des paiements
        $this->command->info("🎫 Creating 100 ticket purchases...");

        for ($i = 1; $i <= 100; $i++) {
            // Créer un utilisateur acheteur
            $buyer = User::create([
                'first_name' => "Acheteur{$i}",
                'last_name' => 'Test',
                'email' => "buyer{$i}.test@koumbaya.com",
                'phone' => '077' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
                'account_type' => 'customer',
                'can_sell' => false,
                'can_buy' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Créer un paiement pour ce ticket
            Payment::create([
                'user_id' => $buyer->id,
                'lottery_id' => $lottery->id,
                'amount' => 1700,
                'currency' => 'XAF',
                'payment_method' => 'mobile_money',
                'payment_provider' => rand(0, 1) ? 'airtel_money' : 'moov_money',
                'status' => 'completed',
                'transaction_id' => 'TXN-TEST-' . $i . '-' . time(),
                'reference' => 'REF-TEST-' . $i . '-' . time(),
                'paid_at' => Carbon::now()->subDays(rand(5, 25)),
                'meta' => json_encode([
                    'ticket_number' => $i,
                    'test_data' => true,
                ]),
            ]);

            if ($i % 20 == 0) {
                $this->command->info("   Created {$i}/100 purchases...");
            }
        }

        $this->command->info("✅ Created 100 ticket purchases");

        // 6. Résumé des données créées
        $this->command->newLine();
        $this->command->info("🎯 TEST LOTTERY CREATED SUCCESSFULLY!");
        $this->command->info("==========================================");
        $this->command->info("Lottery ID: {$lottery->id}");
        $this->command->info("Lottery Number: {$lottery->lottery_number}");
        $this->command->info("Product: {$product->name}");
        $this->command->info("Merchant: {$merchant->email}");
        $this->command->info("Participants: 100/500 (insufficient)");
        $this->command->info("Expired: {$expiryDate->diffForHumans()}");
        $this->command->info("Total to refund: " . number_format(100 * 1700) . " FCFA");
        $this->command->newLine();
        
        $this->command->info("🧪 TEST COMMANDS:");
        $this->command->info("==========================================");
        $this->command->info("# View eligible lotteries:");
        $this->command->info("php artisan lottery:process-expired --dry-run");
        $this->command->newLine();
        
        $this->command->info("# Force manual refund (before 24h delay):");
        $this->command->info("php artisan lottery:process-expired --lottery={$lottery->id} --force");
        $this->command->newLine();
        
        $this->command->info("# Test SHAP API:");
        $this->command->info("php artisan shap:test --balance");
        $this->command->newLine();
        
        $this->command->info("# Admin API endpoint for manual force:");
        $this->command->info("POST /api/admin/refunds/process-automatic");
        $this->command->info("Body: {\"lottery_id\": {$lottery->id}, \"force\": true}");
    }
}