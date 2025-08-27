<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentCallbackFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test callback succès : payment → paid, order → paid, facture dispo
     */
    public function test_successful_callback_payment_to_paid_order_to_paid_invoice_available()
    {
        // Scénario : callback successful avec amount correct
        $payloadData = [
            'reference' => 'TEST-REF-12345',
            'amount' => 5000.00,
            'transactionid' => 'AM-SUCCESS-789',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ];

        // 1. Créer les données de test directement en base
        \DB::table('users')->insert([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('orders')->insert([
            'id' => 1,
            'order_number' => 'ORD-TEST-001',
            'user_id' => 1,
            'type' => 'lottery',
            'status' => 'awaiting_payment',
            'total_amount' => 5000.00,
            'currency' => 'XAF',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('payments')->insert([
            'id' => 1,
            'reference' => 'TEST-REF-12345',
            'user_id' => 1,
            'order_id' => 1,
            'amount' => 5000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'payment_gateway' => 'ebilling',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Envoyer le callback
        $response = $this->postJson('/api/payments/callback', $payloadData);

        // 3. Vérifier la réponse
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

        // 4. Vérifier que payment → paid
        $payment = \DB::table('payments')->where('id', 1)->first();
        $this->assertEquals('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('AM-SUCCESS-789', $payment->external_transaction_id);
        $this->assertEquals('airtel_money', $payment->payment_method);

        // 5. Vérifier que order → paid
        $order = \DB::table('orders')->where('id', 1)->first();
        $this->assertEquals('paid', $order->status);
        $this->assertNotNull($order->paid_at);

        // 6. Vérifier que la facture devient disponible
        // On teste l'endpoint avec une authentification basique
        $token = \DB::table('personal_access_tokens')->insertGetId([
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
            'name' => 'test-token',
            'token' => hash('sha256', 'test-plain-token'),
            'abilities' => json_encode(['*']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $invoiceResponse = $this->withHeaders([
            'Authorization' => 'Bearer test-plain-token'
        ])->get('/api/orders/ORD-TEST-001/invoice');

        $invoiceResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $invoiceResponse->headers->get('content-type'));
    }

    /**
     * Test callback mismatch montant : payment → failed, order reste awaiting_payment, 400
     */
    public function test_callback_amount_mismatch_payment_failed_order_awaiting_payment_400()
    {
        // 1. Créer les données de test
        \DB::table('users')->insert([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('orders')->insert([
            'id' => 1,
            'order_number' => 'ORD-TEST-002',
            'user_id' => 1,
            'type' => 'lottery',
            'status' => 'awaiting_payment',
            'total_amount' => 3000.00,
            'currency' => 'XAF',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('payments')->insert([
            'id' => 1,
            'reference' => 'TEST-REF-MISMATCH',
            'user_id' => 1,
            'order_id' => 1,
            'amount' => 3000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'payment_gateway' => 'ebilling',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Envoyer callback avec montant différent
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-MISMATCH',
            'amount' => 2800.00, // Amount mismatch !
            'transactionid' => 'AM-MISMATCH-456',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        // 3. Vérifier 400 retourné
        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Amount mismatch'
                ]);

        // 4. Vérifier payment → failed
        $payment = \DB::table('payments')->where('id', 1)->first();
        $this->assertEquals('failed', $payment->status);
        $this->assertNull($payment->paid_at);

        // 5. Vérifier order reste awaiting_payment
        $order = \DB::table('orders')->where('id', 1)->first();
        $this->assertEquals('awaiting_payment', $order->status);
        $this->assertNull($order->paid_at);
    }

    /**
     * Test idempotence : second callback idem → 200 "already processed"
     */
    public function test_idempotency_second_callback_returns_200_already_processed()
    {
        // 1. Créer les données de test avec paiement déjà traité
        \DB::table('users')->insert([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('orders')->insert([
            'id' => 1,
            'order_number' => 'ORD-TEST-003',
            'user_id' => 1,
            'type' => 'lottery',
            'status' => 'paid',
            'total_amount' => 2000.00,
            'currency' => 'XAF',
            'paid_at' => now()->subMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('payments')->insert([
            'id' => 1,
            'reference' => 'TEST-REF-IDEMPOTENT',
            'user_id' => 1,
            'order_id' => 1,
            'amount' => 2000.00,
            'currency' => 'XAF',
            'status' => 'paid', // Déjà payé
            'payment_method' => 'airtel_money',
            'external_transaction_id' => 'AM-ORIGINAL-123',
            'paid_at' => now()->subMinutes(10),
            'payment_gateway' => 'ebilling',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Envoyer le même callback une seconde fois
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-IDEMPOTENT',
            'amount' => 2000.00,
            'transactionid' => 'AM-DUPLICATE-456', // Différent transaction ID
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        // 3. Vérifier 200 "already processed"
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Already processed'
                ]);

        // 4. Vérifier payment status inchangé
        $payment = \DB::table('payments')->where('id', 1)->first();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('AM-ORIGINAL-123', $payment->external_transaction_id); // Transaction ID original préservé

        // 5. Vérifier order status inchangé
        $order = \DB::table('orders')->where('id', 1)->first();
        $this->assertEquals('paid', $order->status);
    }

    /**
     * Test sécurité : accès /api/orders* protégé Sanctum (401 sinon)
     */
    public function test_orders_api_endpoints_protected_by_sanctum_401_without_auth()
    {
        // 1. Créer un ordre de test
        \DB::table('users')->insert([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('orders')->insert([
            'id' => 1,
            'order_number' => 'ORD-AUTH-TEST',
            'user_id' => 1,
            'type' => 'lottery',
            'status' => 'paid',
            'total_amount' => 1500.00,
            'currency' => 'XAF',
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Test endpoints SANS authentification → 401

        // GET /api/orders
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401)
                ->assertJson(['message' => 'Unauthenticated.']);

        // GET /api/orders/{order_number}
        $response = $this->getJson('/api/orders/ORD-AUTH-TEST');
        $response->assertStatus(401)
                ->assertJson(['message' => 'Unauthenticated.']);

        // GET /api/orders/{order_number}/invoice
        $response = $this->get('/api/orders/ORD-AUTH-TEST/invoice');
        $response->assertStatus(401)
                ->assertJson(['message' => 'Unauthenticated.']);

        // GET /api/orders/stats
        $response = $this->getJson('/api/orders/stats');
        $response->assertStatus(401)
                ->assertJson(['message' => 'Unauthenticated.']);

        // GET /api/orders/search
        $response = $this->getJson('/api/orders/search?q=test');
        $response->assertStatus(401)
                ->assertJson(['message' => 'Unauthenticated.']);

        // 3. Test AVEC authentification Sanctum → 200

        // Créer un token Sanctum
        \DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
            'name' => 'test-token',
            'token' => hash('sha256', 'valid-auth-token'),
            'abilities' => json_encode(['*']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $headers = ['Authorization' => 'Bearer valid-auth-token'];

        // GET /api/orders avec auth
        $response = $this->withHeaders($headers)->getJson('/api/orders');
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure(['success', 'data', 'pagination']);

        // GET /api/orders/{order_number} avec auth
        $response = $this->withHeaders($headers)->getJson('/api/orders/ORD-AUTH-TEST');
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => ['order_number' => 'ORD-AUTH-TEST']
                ]);

        // GET /api/orders/stats avec auth
        $response = $this->withHeaders($headers)->getJson('/api/orders/stats');
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_orders',
                        'paid_orders',
                        'pending_orders'
                    ]
                ]);
    }
}