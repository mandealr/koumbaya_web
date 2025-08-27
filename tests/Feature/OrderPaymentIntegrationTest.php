<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Lottery;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class OrderPaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;
    protected Lottery $lottery;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@koumbaya.com',
            'phone' => '06123456'
        ]);

        $this->product = Product::factory()->create([
            'name' => 'Test Lottery Product',
            'price' => 1000,
            'sale_mode' => 'lottery'
        ]);

        $this->lottery = Lottery::factory()->create([
            'product_id' => $this->product->id,
            'title' => 'Test Lottery',
            'lottery_number' => 'LOT-TEST-001',
            'ticket_price' => 1000,
            'max_tickets' => 100,
            'status' => 'active'
        ]);
    }

    /**
     * Test successful payment callback updates payment to paid, order to paid, and invoice becomes available
     */
    public function test_successful_callback_payment_to_paid_order_to_paid_invoice_available()
    {
        // Create order and payment
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'lottery_id' => $this->lottery->id,
            'order_number' => 'ORD-TEST-001',
            'type' => 'lottery',
            'status' => OrderStatus::AWAITING_PAYMENT->value,
            'total_amount' => 5000,
            'currency' => 'XAF'
        ]);

        $payment = Payment::create([
            'reference' => 'KMB-PAY-TEST-001',
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'amount' => 5000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'ebilling_id' => 'EB-TEST-123',
            'payment_gateway' => 'ebilling'
        ]);

        // Send successful payment callback
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'KMB-PAY-TEST-001',
            'amount' => 5000.00,
            'transactionid' => 'AM-SUCCESS-456',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        // Assert callback response
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

        // Verify payment status updated to paid
        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('AM-SUCCESS-456', $payment->external_transaction_id);
        $this->assertEquals('airtel_money', $payment->payment_method);

        // Verify order status updated to paid
        $order->refresh();
        $this->assertEquals(OrderStatus::PAID->value, $order->status);
        $this->assertNotNull($order->paid_at);

        // Verify invoice is now available (authenticated request)
        Sanctum::actingAs($this->user);
        
        $invoiceResponse = $this->get("/api/orders/{$order->order_number}/invoice");
        $invoiceResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $invoiceResponse->headers->get('content-type'));
        $this->assertStringContains(
            'attachment; filename=facture-' . $order->order_number . '.pdf',
            $invoiceResponse->headers->get('content-disposition')
        );
    }

    /**
     * Test callback with amount mismatch marks payment as failed, order stays awaiting_payment, returns 400
     */
    public function test_callback_amount_mismatch_payment_failed_order_awaiting_payment_400_response()
    {
        // Create order and payment
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-TEST-002',
            'type' => 'lottery',
            'status' => OrderStatus::AWAITING_PAYMENT->value,
            'total_amount' => 3000,
            'currency' => 'XAF'
        ]);

        $payment = Payment::create([
            'reference' => 'KMB-PAY-TEST-002',
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'amount' => 3000.00, // Expected amount
            'currency' => 'XAF',
            'status' => 'pending',
            'ebilling_id' => 'EB-TEST-456',
            'payment_gateway' => 'ebilling'
        ]);

        // Send callback with mismatched amount
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'KMB-PAY-TEST-002',
            'amount' => 2800.00, // Different amount - mismatch!
            'transactionid' => 'AM-MISMATCH-789',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        // Assert 400 response for amount mismatch
        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Amount mismatch'
                ]);

        // Verify payment marked as failed
        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
        $this->assertNull($payment->paid_at);
        
        // Verify order remains in awaiting_payment status
        $order->refresh();
        $this->assertEquals(OrderStatus::AWAITING_PAYMENT->value, $order->status);
        $this->assertNull($order->paid_at);

        // Verify invoice is NOT available
        Sanctum::actingAs($this->user);
        
        $invoiceResponse = $this->get("/api/orders/{$order->order_number}/invoice");
        $invoiceResponse->assertStatus(400) // Bad request - order not paid
                       ->assertJson([
                           'success' => false,
                           'message' => 'Erreur lors de la génération de la facture'
                       ]);
    }

    /**
     * Test idempotency - second identical callback returns 200 "already processed"
     */
    public function test_idempotency_second_callback_identical_returns_200_already_processed()
    {
        // Create order and payment (already paid)
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-TEST-003',
            'type' => 'lottery',
            'status' => OrderStatus::PAID->value,
            'total_amount' => 2000,
            'currency' => 'XAF',
            'paid_at' => now()->subMinutes(10)
        ]);

        $payment = Payment::create([
            'reference' => 'KMB-PAY-TEST-003',
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'amount' => 2000.00,
            'currency' => 'XAF',
            'status' => 'paid', // Already paid
            'paid_at' => now()->subMinutes(10),
            'external_transaction_id' => 'AM-ORIGINAL-123',
            'payment_method' => 'airtel_money',
            'ebilling_id' => 'EB-TEST-789',
            'payment_gateway' => 'ebilling'
        ]);

        // Send identical callback again (should be idempotent)
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'KMB-PAY-TEST-003',
            'amount' => 2000.00,
            'transactionid' => 'AM-DUPLICATE-456', // Different transaction ID
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        // Assert 200 response with "already processed"
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Already processed'
                ]);

        // Verify payment status unchanged
        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('AM-ORIGINAL-123', $payment->external_transaction_id); // Original transaction ID preserved
        $this->assertEquals('airtel_money', $payment->payment_method);
        
        // Verify order status unchanged
        $order->refresh();
        $this->assertEquals(OrderStatus::PAID->value, $order->status);
        $this->assertNotNull($order->paid_at);
    }

    /**
     * Test orders API endpoints require Sanctum authentication (401 without auth)
     */
    public function test_orders_api_endpoints_protected_sanctum_401_without_auth()
    {
        // Create a test order
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST-004',
            'status' => OrderStatus::PAID->value
        ]);

        // Test GET /api/orders without authentication
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);

        // Test GET /api/orders/{order_number} without authentication
        $response = $this->getJson("/api/orders/{$order->order_number}");
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);

        // Test GET /api/orders/{order_number}/invoice without authentication
        $response = $this->get("/api/orders/{$order->order_number}/invoice");
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);

        // Test GET /api/orders/stats without authentication
        $response = $this->getJson('/api/orders/stats');
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);

        // Test GET /api/orders/search without authentication
        $response = $this->getJson('/api/orders/search?q=test');
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /**
     * Test orders API endpoints work correctly with Sanctum authentication
     */
    public function test_orders_api_endpoints_work_with_sanctum_authentication()
    {
        // Create test orders for the authenticated user
        $order1 = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'lottery_id' => $this->lottery->id,
            'order_number' => 'ORD-AUTH-001',
            'type' => 'lottery',
            'status' => OrderStatus::PAID->value,
            'total_amount' => 3000,
            'paid_at' => now()
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-AUTH-002',
            'type' => 'direct',
            'status' => OrderStatus::PENDING->value,
            'total_amount' => 1500
        ]);

        // Authenticate user with Sanctum
        Sanctum::actingAs($this->user);

        // Test GET /api/orders with authentication
        $response = $this->getJson('/api/orders');
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'order_number',
                            'type',
                            'status',
                            'total_amount',
                            'currency',
                            'created_at'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]);

        // Verify only user's orders are returned
        $responseData = $response->json('data');
        $this->assertCount(2, $responseData);
        $this->assertTrue(collect($responseData)->every(fn($order) => 
            in_array($order['order_number'], ['ORD-AUTH-001', 'ORD-AUTH-002'])
        ));

        // Test GET /api/orders/{order_number} with authentication
        $response = $this->getJson("/api/orders/{$order1->order_number}");
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'order_number' => 'ORD-AUTH-001',
                        'type' => 'lottery',
                        'status' => 'paid',
                        'total_amount' => 3000
                    ]
                ]);

        // Test GET /api/orders/stats with authentication
        $response = $this->getJson('/api/orders/stats');
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_orders',
                        'paid_orders',
                        'pending_orders',
                        'failed_orders',
                        'fulfilled_orders',
                        'total_amount_spent',
                        'lottery_orders',
                        'direct_orders',
                        'total_tickets_purchased',
                        'winning_tickets'
                    ]
                ]);

        // Test GET /api/orders/search with authentication
        $response = $this->getJson('/api/orders/search?q=AUTH');
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data'
                ]);
    }

    /**
     * Test user can only access their own orders (authorization)
     */
    public function test_user_can_only_access_own_orders()
    {
        // Create another user with their own order
        $otherUser = User::factory()->create();
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'order_number' => 'ORD-OTHER-001',
            'status' => OrderStatus::PAID->value
        ]);

        // Create order for current user
        $userOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-USER-001',
            'status' => OrderStatus::PAID->value
        ]);

        // Authenticate as current user
        Sanctum::actingAs($this->user);

        // Try to access other user's order - should return 404 (not found, not forbidden)
        $response = $this->getJson("/api/orders/{$otherOrder->order_number}");
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Commande non trouvée'
                ]);

        // Can access own order
        $response = $this->getJson("/api/orders/{$userOrder->order_number}");
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'order_number' => 'ORD-USER-001'
                    ]
                ]);

        // Try to access other user's invoice - should return 404
        $response = $this->get("/api/orders/{$otherOrder->order_number}/invoice");
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Commande non trouvée'
                ]);
    }

    /**
     * Test invoice is only available for paid orders
     */
    public function test_invoice_only_available_for_paid_orders()
    {
        // Create orders with different statuses
        $pendingOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-PENDING-001',
            'status' => OrderStatus::PENDING->value
        ]);

        $paidOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-PAID-001',
            'status' => OrderStatus::PAID->value,
            'paid_at' => now()
        ]);

        $fulfilledOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-FULFILLED-001',
            'status' => OrderStatus::FULFILLED->value,
            'paid_at' => now(),
            'fulfilled_at' => now()
        ]);

        // Authenticate user
        Sanctum::actingAs($this->user);

        // Test invoice not available for pending order
        $response = $this->get("/api/orders/{$pendingOrder->order_number}/invoice");
        $response->assertStatus(400);

        // Test invoice available for paid order
        $response = $this->get("/api/orders/{$paidOrder->order_number}/invoice");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

        // Test invoice available for fulfilled order
        $response = $this->get("/api/orders/{$fulfilledOrder->order_number}/invoice");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * Test complex callback scenario with order status mapping
     */
    public function test_callback_with_payment_status_mapping()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-MAP-001',
            'status' => OrderStatus::AWAITING_PAYMENT->value,
            'total_amount' => 4000
        ]);

        $payment = Payment::create([
            'reference' => 'KMB-PAY-MAP-001',
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'amount' => 4000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'payment_gateway' => 'ebilling'
        ]);

        // Test various gateway status mappings
        $testCases = [
            ['gateway_status' => 'success', 'expected_payment' => 'paid', 'expected_order' => OrderStatus::PAID->value],
            ['gateway_status' => 'paid', 'expected_payment' => 'paid', 'expected_order' => OrderStatus::PAID->value],
            ['gateway_status' => 'completed', 'expected_payment' => 'paid', 'expected_order' => OrderStatus::PAID->value],
            ['gateway_status' => 'failed', 'expected_payment' => 'failed', 'expected_order' => OrderStatus::FAILED->value],
            ['gateway_status' => 'error', 'expected_payment' => 'failed', 'expected_order' => OrderStatus::FAILED->value],
            ['gateway_status' => 'cancelled', 'expected_payment' => 'failed', 'expected_order' => OrderStatus::FAILED->value],
        ];

        foreach ($testCases as $case) {
            // Reset payment and order
            $payment->update(['status' => 'pending', 'paid_at' => null]);
            $order->update(['status' => OrderStatus::AWAITING_PAYMENT->value, 'paid_at' => null]);

            // Send callback
            $response = $this->postJson('/api/payments/callback', [
                'reference' => 'KMB-PAY-MAP-001',
                'amount' => 4000.00,
                'transactionid' => 'TXN-' . strtoupper($case['gateway_status']) . '-123',
                'paymentsystem' => 'moovmoney',
                'status' => $case['gateway_status']
            ]);

            $response->assertStatus(200);

            $payment->refresh();
            $order->refresh();

            $this->assertEquals($case['expected_payment'], $payment->status, 
                "Payment status should be {$case['expected_payment']} for gateway status {$case['gateway_status']}");
            $this->assertEquals($case['expected_order'], $order->status,
                "Order status should be {$case['expected_order']} for gateway status {$case['gateway_status']}");
        }
    }
}