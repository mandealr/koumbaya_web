<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable logs during testing
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);
    }

    public function test_successful_payment_callback()
    {
        // Create test data
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $payment = Payment::create([
            'reference' => 'TEST-REF-001',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 1000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'ebilling_id' => 'BILL-123',
            'payment_gateway' => 'ebilling',
        ]);

        // Test successful callback
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-001',
            'amount' => 1000.00,
            'transactionid' => 'TXN-SUCCESS-456',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

        // Verify payment was updated
        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('TXN-SUCCESS-456', $payment->external_transaction_id);
        $this->assertEquals('airtelmoney', $payment->payment_method);

        // Verify order was updated
        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertEquals('TXN-SUCCESS-456', $order->payment_reference);
    }

    public function test_failed_payment_callback()
    {
        // Create test data
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $payment = Payment::create([
            'reference' => 'TEST-REF-002',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 1500.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'payment_gateway' => 'ebilling',
        ]);

        // Test failed callback
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-002',
            'amount' => 1500.00,
            'transactionid' => 'TXN-FAIL-789',
            'paymentsystem' => 'moovmoney',
            'status' => 'failed'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

        // Verify payment was updated
        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
        $this->assertNull($payment->paid_at);

        // Verify order was updated
        $order->refresh();
        $this->assertEquals('failed', $order->status);
    }

    public function test_amount_mismatch_callback()
    {
        // Create test data
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $payment = Payment::create([
            'reference' => 'TEST-REF-003',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 2000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'payment_gateway' => 'ebilling',
        ]);

        // Test amount mismatch
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-003',
            'amount' => 1800.00, // Different amount
            'transactionid' => 'TXN-MISMATCH-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Amount mismatch'
                ]);

        // Verify payment was marked as failed
        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
        $this->assertArrayHasKey('error', $payment->gateway_response);
        $this->assertEquals('amount_mismatch', $payment->gateway_response['error']);
    }

    public function test_payment_not_found_callback()
    {
        // Test with non-existent reference
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'NON-EXISTENT-REF',
            'amount' => 1000.00,
            'transactionid' => 'TXN-404',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Payment not found'
                ]);
    }

    public function test_invalid_payload_callback()
    {
        // Test with missing required fields
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-004',
            // Missing amount, transactionid, paymentsystem
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid payload'
                ]);
    }

    public function test_pending_status_callback()
    {
        // Create test data
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $payment = Payment::create([
            'reference' => 'TEST-REF-005',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 3000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'payment_gateway' => 'ebilling',
        ]);

        // Test pending callback
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-005',
            'amount' => 3000.00,
            'transactionid' => 'TXN-PENDING-999',
            'paymentsystem' => 'moovmoney',
            'status' => 'processing'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

        // Verify payment status
        $payment->refresh();
        $this->assertEquals('pending', $payment->status);
        $this->assertNull($payment->paid_at);

        // Verify order moved to awaiting payment
        $order->refresh();
        $this->assertEquals('awaiting_payment', $order->status);
    }

    public function test_callback_with_ebilling_id()
    {
        // Create test data
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $payment = Payment::create([
            'reference' => 'TEST-REF-006',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 5000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'ebilling_id' => 'EBILL-SEARCH-123',
            'payment_gateway' => 'ebilling',
        ]);

        // Test callback using ebilling_id as reference
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'EBILL-SEARCH-123', // Using ebilling_id
            'amount' => 5000.00,
            'transactionid' => 'TXN-EBILL-456',
            'paymentsystem' => 'airtelmoney',
            'status' => 'paid'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

        // Verify payment was found and updated
        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
    }

    public function test_idempotent_callback_for_already_paid_payment()
    {
        // Create test data with already paid payment
        $user = User::factory()->create();
        $order = Order::factory()->paid()->create(['user_id' => $user->id]);
        $payment = Payment::create([
            'reference' => 'TEST-REF-007',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 2500.00,
            'currency' => 'XAF',
            'status' => 'paid', // Already paid
            'paid_at' => now()->subMinutes(5),
            'payment_gateway' => 'ebilling',
        ]);

        // Test callback for already paid payment
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-007',
            'amount' => 2500.00,
            'transactionid' => 'TXN-DUPLICATE-789',
            'paymentsystem' => 'moovmoney',
            'status' => 'success'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Already processed'
                ]);

        // Verify payment status didn't change
        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_idempotent_callback_for_already_failed_payment()
    {
        // Create test data with already failed payment
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'failed']);
        $payment = Payment::create([
            'reference' => 'TEST-REF-008',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 1200.00,
            'currency' => 'XAF',
            'status' => 'failed', // Already failed
            'payment_gateway' => 'ebilling',
        ]);

        // Test callback for already failed payment
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-008',
            'amount' => 1200.00,
            'transactionid' => 'TXN-RETRY-456',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Already processed'
                ]);

        // Verify payment status didn't change
        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
    }

    public function test_callback_stores_security_information()
    {
        // Create test data
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        $payment = Payment::create([
            'reference' => 'TEST-REF-009',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 800.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'payment_gateway' => 'ebilling',
        ]);

        // Test callback with custom headers
        $response = $this->withHeaders([
            'User-Agent' => 'E-Billing-Webhook/1.0',
            'X-Forwarded-For' => '192.168.1.100',
        ])->postJson('/api/payments/callback', [
            'reference' => 'TEST-REF-009',
            'amount' => 800.00,
            'transactionid' => 'TXN-SECURITY-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        $response->assertStatus(200);

        // Verify security information was stored
        $payment->refresh();
        $this->assertArrayHasKey('security', $payment->callback_data);
        $this->assertEquals('E-Billing-Webhook/1.0', $payment->callback_data['security']['user_agent']);
        $this->assertNotNull($payment->callback_data['security']['ip']);
        $this->assertNotNull($payment->callback_data['security']['timestamp']);
    }
}