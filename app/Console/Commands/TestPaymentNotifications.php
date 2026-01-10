<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Lottery;
use App\Mail\PaymentConfirmation;
use App\Mail\MerchantPaymentNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestPaymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payment-notifications
                            {email? : Email address to send test notifications to}
                            {--type=all : Type of notification (customer|merchant|admin|all)}
                            {--scenario=lottery : Payment scenario (lottery|direct|custom)}
                            {--merchant-email= : Merchant email for merchant notification test}
                            {--no-admin : Do not send copy to admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test payment notification emails (customer, merchant and admin)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Test des notifications de paiement - Koumbaya');
        $this->newLine();

        // Récupérer les paramètres
        $email = $this->argument('email');
        $type = $this->option('type');
        $scenario = $this->option('scenario');
        $merchantEmail = $this->option('merchant-email');

        // Si aucun email fourni, demander
        if (!$email) {
            $email = $this->ask('📧 Entrez l\'adresse email pour le test', 'test@example.com');
        }

        // Valider l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('❌ Adresse email invalide');
            return 1;
        }

        $this->info("📨 Email de test : {$email}");
        $this->info("📋 Type : {$type}");
        $this->info("🎭 Scénario : {$scenario}");
        $this->newLine();

        // Confirmer avant d'envoyer
        if (!$this->confirm('Voulez-vous envoyer les emails de test ?', true)) {
            $this->warn('⚠️ Test annulé');
            return 0;
        }

        $this->newLine();
        $this->info('⏳ Préparation des données de test...');

        try {
            // Créer ou récupérer des données de test
            $testData = $this->createTestData($email, $merchantEmail, $scenario);

            // Envoyer les notifications selon le type
            $sent = 0;

            if ($type === 'customer' || $type === 'all') {
                $this->info('📤 Envoi de la notification client...');
                $this->sendCustomerNotification($testData);
                $sent++;
                $this->info('✅ Notification client envoyée');
            }

            if ($type === 'merchant' || $type === 'all') {
                $this->info('📤 Envoi de la notification marchand...');
                $this->sendMerchantNotification($testData);
                $sent++;
                $this->info('✅ Notification marchand envoyée');
            }

            // Envoyer une copie à l'admin (sauf si --no-admin)
            if (!$this->option('no-admin') && ($type === 'admin' || $type === 'all')) {
                $this->info('📤 Envoi de la copie admin...');
                $this->sendAdminNotification($testData);
                $sent++;
                $this->info('✅ Copie admin envoyée');
            }

            $this->newLine();
            $this->info("🎉 Test terminé avec succès ! {$sent} email(s) envoyé(s)");
            $this->displayTestSummary($testData, $email, $merchantEmail);

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Erreur lors du test : ' . $e->getMessage());
            $this->error('📍 Fichier : ' . $e->getFile() . ':' . $e->getLine());

            if ($this->option('verbose')) {
                $this->error('📚 Stack trace :');
                $this->line($e->getTraceAsString());
            }

            Log::error('Erreur test payment notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }

    /**
     * Créer des données de test
     */
    protected function createTestData($email, $merchantEmail, $scenario)
    {
        // Créer/récupérer un utilisateur de test
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'phone' => '+241074445566',
                'password' => bcrypt('password'),
                'user_type_id' => 2, // customer
                'country_id' => 1,
                'language_id' => 1,
                'is_active' => true,
                'is_email_verified' => true,
            ]
        );

        // Créer/récupérer un marchand de test
        $merchantUserEmail = $merchantEmail ?: 'merchant@koumbaya.com';
        $merchant = User::firstOrCreate(
            ['email' => $merchantUserEmail],
            [
                'first_name' => 'Marchand',
                'last_name' => 'Test',
                'phone' => '+241074445577',
                'password' => bcrypt('password'),
                'user_type_id' => 3, // merchant
                'country_id' => 1,
                'language_id' => 1,
                'is_active' => true,
                'is_email_verified' => true,
            ]
        );

        // Créer un produit de test
        $product = Product::firstOrCreate(
            [
                'name' => 'Produit Test - iPhone 15 Pro',
                'merchant_id' => $merchant->id
            ],
            [
                'description' => 'iPhone 15 Pro 256GB - Produit de test pour notifications',
                'price' => 850000,
                'currency' => 'XAF',
                'category_id' => 1,
                'stock_quantity' => 10,
                'is_active' => true,
                'is_featured' => false,
                'sale_mode' => $scenario === 'lottery' ? 'lottery' : 'direct',
            ]
        );

        // Créer une loterie si nécessaire
        $lottery = null;
        if ($scenario === 'lottery') {
            $lottery = Lottery::firstOrCreate(
                [
                    'lottery_number' => 'TEST-' . strtoupper(substr(md5(time()), 0, 8)),
                ],
                [
                    'title' => 'Tombola Test - iPhone 15 Pro',
                    'description' => 'Tombola de test pour notifications',
                    'product_id' => $product->id,
                    'ticket_price' => 5000,
                    'currency' => 'XAF',
                    'max_tickets' => 100,
                    'sold_tickets' => 25,
                    'draw_date' => now()->addDays(7),
                    'status' => 'active',
                ]
            );
        }

        // Créer une commande de test
        $order = Order::create([
            'order_number' => 'ORD-TEST-' . strtoupper(substr(md5(time()), 0, 10)),
            'user_id' => $user->id,
            'type' => $scenario === 'lottery' ? 'lottery' : 'direct',
            'product_id' => $product->id,
            'lottery_id' => $lottery ? $lottery->id : null,
            'total_amount' => $scenario === 'lottery' ? 5000 : $product->price,
            'currency' => 'XAF',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Créer un paiement de test
        $payment = Payment::create([
            'reference' => 'PAY-TEST-' . strtoupper(substr(md5(time()), 0, 10)),
            'order_id' => $order->id,
            'user_id' => $user->id,
            'ebilling_id' => 'EBILL-TEST-' . rand(10000, 99999),
            'external_transaction_id' => 'EXT-' . rand(100000, 999999),
            'payment_method' => 'airtel_money',
            'amount' => $order->total_amount,
            'currency' => 'XAF',
            'status' => 'paid',
            'paid_at' => now(),
            'meta' => json_encode([
                'customer_name' => $user->full_name,
                'customer_phone' => $user->phone,
                'description' => $scenario === 'lottery'
                    ? "Achat de ticket - Tombola {$lottery->lottery_number}"
                    : "Achat direct - {$product->name}",
                'test_mode' => true,
            ]),
        ]);

        return [
            'user' => $user,
            'merchant' => $merchant,
            'product' => $product,
            'lottery' => $lottery,
            'order' => $order,
            'payment' => $payment,
            'scenario' => $scenario,
        ];
    }

    /**
     * Envoyer la notification au client
     */
    protected function sendCustomerNotification($testData)
    {
        $payment = $testData['payment'];
        $user = $testData['user'];

        Log::info('TEST :: Envoi notification paiement client', [
            'payment_id' => $payment->id,
            'user_email' => $user->email,
            'amount' => $payment->amount,
            'scenario' => $testData['scenario']
        ]);

        // Envoyer l'email
        Mail::to($user->email)->send(new PaymentConfirmation($payment));

        // Afficher un aperçu des données
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['👤 Client', $user->full_name],
                ['📧 Email', $user->email],
                ['💰 Montant', number_format($payment->amount, 0, ',', ' ') . ' XAF'],
                ['🔖 Référence', $payment->reference],
                ['📱 Méthode', ucfirst(str_replace('_', ' ', $payment->payment_method))],
                ['📦 Commande', $testData['order']->order_number],
                ['🛍️ Produit', $testData['product']->name],
            ]
        );
    }

    /**
     * Envoyer la notification au marchand
     */
    protected function sendMerchantNotification($testData)
    {
        $payment = $testData['payment'];
        $merchant = $testData['merchant'];
        $product = $testData['product'];

        Log::info('TEST :: Envoi notification paiement marchand', [
            'payment_id' => $payment->id,
            'merchant_email' => $merchant->email,
            'product_id' => $product->id,
            'amount' => $payment->amount,
            'scenario' => $testData['scenario']
        ]);

        // Envoyer l'email
        Mail::to($merchant->email)->send(new MerchantPaymentNotification($payment));

        // Afficher un aperçu des données
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['🏪 Marchand', $merchant->full_name],
                ['📧 Email', $merchant->email],
                ['💰 Montant', number_format($payment->amount, 0, ',', ' ') . ' XAF'],
                ['🔖 Référence', $payment->reference],
                ['🛍️ Produit', $product->name],
                ['👤 Client', $testData['user']->full_name],
                ['📦 Commande', $testData['order']->order_number],
            ]
        );
    }

    /**
     * Envoyer une copie à l'admin
     */
    protected function sendAdminNotification($testData)
    {
        $payment = $testData['payment'];
        $adminEmail = config('mail.admin_email', 'admin@koumbaya.com');

        Log::info('TEST :: Envoi copie admin', [
            'payment_id' => $payment->id,
            'admin_email' => $adminEmail,
            'amount' => $payment->amount,
            'scenario' => $testData['scenario']
        ]);

        // Envoyer les deux notifications à l'admin pour qu'il puisse voir les deux templates
        Mail::to($adminEmail)
            ->cc($adminEmail) // Mettre en copie pour s'assurer
            ->send(new PaymentConfirmation($payment));

        // Afficher un aperçu des données
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['👨‍💼 Admin', 'Administrateur Koumbaya'],
                ['📧 Email', $adminEmail],
                ['💰 Montant', number_format($payment->amount, 0, ',', ' ') . ' XAF'],
                ['🔖 Référence', $payment->reference],
                ['📦 Commande', $testData['order']->order_number],
                ['👤 Client', $testData['user']->full_name],
                ['🏪 Marchand', $testData['merchant']->full_name],
            ]
        );
    }

    /**
     * Afficher le résumé du test
     */
    protected function displayTestSummary($testData, $email, $merchantEmail)
    {
        $this->newLine();
        $this->info('📊 Résumé du test :');
        $this->newLine();

        $this->line('🎯 <comment>Scénario :</comment> ' . ucfirst($testData['scenario']));
        $this->line('👤 <comment>Email client :</comment> ' . $email);
        $this->line('🏪 <comment>Email marchand :</comment> ' . ($merchantEmail ?: 'merchant@koumbaya.com'));

        if (!$this->option('no-admin')) {
            $adminEmail = config('mail.admin_email', 'admin@koumbaya.com');
            $this->line('👨‍💼 <comment>Email admin :</comment> ' . $adminEmail);
        }

        $this->newLine();

        $this->line('💳 <comment>Détails paiement :</comment>');
        $this->line('   • Montant : ' . number_format($testData['payment']->amount, 0, ',', ' ') . ' XAF');
        $this->line('   • Référence : ' . $testData['payment']->reference);
        $this->line('   • Commande : ' . $testData['order']->order_number);
        $this->line('   • Produit : ' . $testData['product']->name);

        if ($testData['lottery']) {
            $this->line('   • Tombola : ' . $testData['lottery']->lottery_number);
        }

        $this->newLine();
        $this->info('📬 Vérifiez votre boîte mail (y compris les spams)');
        $this->info('📝 Consultez les logs : storage/logs/laravel.log');
        $this->newLine();

        $this->comment('💡 Commandes utiles :');
        $this->line('   • Voir les logs : tail -f storage/logs/laravel.log | grep "TEST ::"');
        $this->line('   • Réexécuter : php artisan test:payment-notifications ' . $email);
        $this->line('   • Aide : php artisan test:payment-notifications --help');
    }
}
