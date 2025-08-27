<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Lottery;
use App\Models\Category;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Log;

class OrderPaymentCallbackTest extends TestCase
{
    use DatabaseTransactions; // Use transactions instead of RefreshDatabase to avoid migration issues

    protected User $user;
    protected Order $order;
    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable logs during testing
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);

        // Create test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@koumbaya.com',
            'phone' => '06123456',
            'password' => bcrypt('password'),
            'user_type_id' => 2, // Assuming customer type
            'country_id' => 1,
            'language_id' => 1,
            'is_phone_verified' => true,
            'is_email_verified' => true,
        ]);

        // Create basic test data
        $this->order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST-001',
            'type' => 'lottery',
            'status' => OrderStatus::AWAITING_PAYMENT->value,
            'total_amount' => 5000,
            'currency' => 'XAF'
        ]);

        $this->payment = Payment::create([
            'reference' => 'KMB-PAY-TEST-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => 5000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'ebilling_id' => 'EB-TEST-123',
            'payment_gateway' => 'ebilling'
        ]);
    }

    /**
     * Test successful payment callback updates payment to paid, order to paid
     */
    public function test_successful_callback_updates_payment_and_order_to_paid()
    {
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
        $this->payment->refresh();
        $this->assertEquals('paid', $this->payment->status);
        $this->assertNotNull($this->payment->paid_at);
        $this->assertEquals('AM-SUCCESS-456', $this->payment->external_transaction_id);
        $this->assertEquals('airtel_money', $this->payment->payment_method);

        // Verify order status updated to paid
        $this->order->refresh();
        $this->assertEquals(OrderStatus::PAID->value, $this->order->status);
        $this->assertNotNull($this->order->paid_at);
    }

    /**
     * Test callback with amount mismatch marks payment as failed, order stays awaiting_payment, returns 400
     */
    public function test_callback_amount_mismatch_returns_400_payment_failed()
    {
        // Send callback with mismatched amount
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'KMB-PAY-TEST-001',
            'amount' => 4500.00, // Different amount - mismatch!
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
        $this->payment->refresh();
        $this->assertEquals('failed', $this->payment->status);
        $this->assertNull($this->payment->paid_at);
        
        // Verify order remains in awaiting_payment status
        $this->order->refresh();
        $this->assertEquals(OrderStatus::AWAITING_PAYMENT->value, $this->order->status);
        $this->assertNull($this->order->paid_at);
    }

    /**
     * Test idempotency - second identical callback returns 200 "already processed"
     */
    public function test_idempotency_second_callback_returns_200_already_processed()
    {
        // First, process the payment successfully
        $this->payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'external_transaction_id' => 'AM-ORIGINAL-123',
            'payment_method' => 'airtel_money'
        ]);
        
        $this->order->update([
            'status' => OrderStatus::PAID->value,
            'paid_at' => now()
        ]);

        // Send identical callback again (should be idempotent)
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'KMB-PAY-TEST-001',
            'amount' => 5000.00,
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
        $this->payment->refresh();
        $this->assertEquals('paid', $this->payment->status);
        $this->assertEquals('AM-ORIGINAL-123', $this->payment->external_transaction_id); // Original preserved
        
        // Verify order status unchanged
        $this->order->refresh();
        $this->assertEquals(OrderStatus::PAID->value, $this->order->status);
    }

    /**
     * Test orders API endpoints require Sanctum authentication (401 without auth)
     */
    public function test_orders_api_endpoints_require_authentication()
    {
        // Test GET /api/orders without authentication
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);

        // Test GET /api/orders/{order_number} without authentication
        $response = $this->getJson("/api/orders/{$this->order->order_number}");
        $response->assertStatus(401);

        // Test GET /api/orders/stats without authentication
        $response = $this->getJson('/api/orders/stats');
        $response->assertStatus(401);
    }

    /**
     * Test orders API endpoints work with Sanctum authentication
     */
    public function test_orders_api_endpoints_work_with_authentication()
    {
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
                    'data',
                    'pagination'
                ]);

        // Test GET /api/orders/{order_number} with authentication
        $response = $this->getJson("/api/orders/{$this->order->order_number}");
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'order_number' => $this->order->order_number,
                        'status' => OrderStatus::AWAITING_PAYMENT->value
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
                        'pending_orders'
                    ]
                ]);
    }

    /**
     * Test invoice is only available for paid orders
     */
    public function test_invoice_only_available_for_paid_orders()
    {
        // Authenticate user
        Sanctum::actingAs($this->user);

        // Test invoice not available for awaiting_payment order
        $response = $this->get("/api/orders/{$this->order->order_number}/invoice");
        $response->assertStatus(400); // Should fail because order is not paid

        // Update order to paid
        $this->order->update([
            'status' => OrderStatus::PAID->value,
            'paid_at' => now()
        ]);

        // Test invoice available for paid order
        $response = $this->get("/api/orders/{$this->order->order_number}/invoice");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * Test user can only access their own orders
     */
    public function test_user_can_only_access_own_orders()
    {
        // Create another user with their own order
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@koumbaya.com',
            'phone' => '06654321',
            'password' => bcrypt('password'),
            'user_type_id' => 2,
            'country_id' => 1,
            'language_id' => 1,
            'is_phone_verified' => true,
            'is_email_verified' => true,
        ]);

        $otherOrder = Order::create([
            'user_id' => $otherUser->id,
            'order_number' => 'ORD-OTHER-001',
            'type' => 'lottery',
            'status' => OrderStatus::PAID->value,
            'total_amount' => 3000,
            'currency' => 'XAF',
            'paid_at' => now()
        ]);

        // Authenticate as current user
        Sanctum::actingAs($this->user);

        // Try to access other user's order - should return 404
        $response = $this->getJson("/api/orders/{$otherOrder->order_number}");
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Commande non trouvée'
                ]);

        // Can access own order
        $response = $this->getJson("/api/orders/{$this->order->order_number}");
        $response->assertStatus(200);
    }

    /**
     * Test callback with different payment status mappings
     */
    public function test_callback_payment_status_mapping()
    {
        $testCases = [
            ['gateway_status' => 'success', 'expected_payment' => 'paid', 'expected_order' => OrderStatus::PAID->value],
            ['gateway_status' => 'paid', 'expected_payment' => 'paid', 'expected_order' => OrderStatus::PAID->value],
            ['gateway_status' => 'completed', 'expected_payment' => 'paid', 'expected_order' => OrderStatus::PAID->value],
            ['gateway_status' => 'failed', 'expected_payment' => 'failed', 'expected_order' => OrderStatus::FAILED->value],
            ['gateway_status' => 'error', 'expected_payment' => 'failed', 'expected_order' => OrderStatus::FAILED->value],
            ['gateway_status' => 'cancelled', 'expected_payment' => 'failed', 'expected_order' => OrderStatus::FAILED->value],
        ];

        foreach ($testCases as $index => $case) {
            // Reset payment and order for each test
            $this->payment->update(['status' => 'pending', 'paid_at' => null]);
            $this->order->update(['status' => OrderStatus::AWAITING_PAYMENT->value, 'paid_at' => null]);

            // Send callback with different status
            $response = $this->postJson('/api/payments/callback', [
                'reference' => 'KMB-PAY-TEST-001',
                'amount' => 5000.00,
                'transactionid' => 'TXN-' . strtoupper($case['gateway_status']) . '-' . $index,
                'paymentsystem' => 'airtelmoney',
                'status' => $case['gateway_status']
            ]);

            $response->assertStatus(200);

            // Verify mappings
            $this->payment->refresh();
            $this->order->refresh();

            $this->assertEquals($case['expected_payment'], $this->payment->status, 
                "Payment status should be {$case['expected_payment']} for gateway status {$case['gateway_status']}");
            $this->assertEquals($case['expected_order'], $this->order->status,
                "Order status should be {$case['expected_order']} for gateway status {$case['gateway_status']}");
        }
    }
}