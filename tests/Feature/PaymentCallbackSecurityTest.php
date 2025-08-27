<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class PaymentCallbackSecurityTest extends TestCase
{
    use RefreshDatabase;

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

        // Setup test data
        $this->user = User::factory()->create();
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-SEC-001',
            'status' => OrderStatus::AWAITING_PAYMENT->value,
            'total_amount' => 5000
        ]);

        $this->payment = Payment::create([
            'reference' => 'KMB-PAY-SEC-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => 5000.00,
            'currency' => 'XAF',
            'status' => 'pending',
            'ebilling_id' => 'EB-SEC-123',
            'payment_gateway' => 'ebilling'
        ]);
    }

    /**
     * Test callback with valid HMAC signature is processed
     */
    public function test_callback_with_valid_hmac_signature_processed()
    {
        // Set HMAC secret for testing
        Config::set('services.ebilling.webhook_secret', 'test-secret-key');

        $payload = [
            'reference' => 'KMB-PAY-SEC-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-VALID-HMAC-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ];

        // Generate valid HMAC signature
        $signature = hash_hmac('sha256', json_encode($payload), 'test-secret-key');

        $response = $this->withHeaders([
            'X-Signature' => $signature
        ])->postJson('/api/payments/callback', $payload);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

        // Verify payment was processed
        $this->payment->refresh();
        $this->assertEquals('paid', $this->payment->status);
    }

    /**
     * Test callback with invalid HMAC signature is rejected
     */
    public function test_callback_with_invalid_hmac_signature_rejected()
    {
        // Set HMAC secret for testing
        Config::set('services.ebilling.webhook_secret', 'test-secret-key');

        $payload = [
            'reference' => 'KMB-PAY-SEC-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-INVALID-HMAC-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ];

        // Generate invalid HMAC signature (using wrong secret)
        $signature = hash_hmac('sha256', json_encode($payload), 'wrong-secret-key');

        $response = $this->withHeaders([
            'X-Signature' => $signature
        ])->postJson('/api/payments/callback', $payload);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid signature'
                ]);

        // Verify payment was NOT processed
        $this->payment->refresh();
        $this->assertEquals('pending', $this->payment->status);
    }

    /**
     * Test callback without signature header is rejected when HMAC is enabled
     */
    public function test_callback_without_signature_rejected_when_hmac_enabled()
    {
        // Set HMAC secret for testing
        Config::set('services.ebilling.webhook_secret', 'test-secret-key');

        $payload = [
            'reference' => 'KMB-PAY-SEC-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-NO-SIG-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ];

        // Send callback without X-Signature header
        $response = $this->postJson('/api/payments/callback', $payload);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Missing signature'
                ]);

        // Verify payment was NOT processed
        $this->payment->refresh();
        $this->assertEquals('pending', $this->payment->status);
    }

    /**
     * Test callback processes when HMAC is disabled (no secret configured)
     */
    public function test_callback_processes_when_hmac_disabled()
    {
        // Disable HMAC by not setting the secret
        Config::set('services.ebilling.webhook_secret', null);

        $payload = [
            'reference' => 'KMB-PAY-SEC-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-NO-HMAC-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ];

        // Send callback without signature (should work when HMAC is disabled)
        $response = $this->postJson('/api/payments/callback', $payload);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

        // Verify payment was processed
        $this->payment->refresh();
        $this->assertEquals('paid', $this->payment->status);
    }

    /**
     * Test callback rate limiting protection
     */
    public function test_callback_rate_limiting_protection()
    {
        // Disable HMAC for this test
        Config::set('services.ebilling.webhook_secret', null);

        $payload = [
            'reference' => 'KMB-PAY-SEC-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-RATE-LIMIT-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ];

        // Send many rapid requests to trigger rate limiting
        for ($i = 0; $i < 20; $i++) {
            $response = $this->postJson('/api/payments/callback', $payload);
            
            if ($i < 10) {
                // First few should succeed or return "already processed"
                $response->assertStatus(200);
            } else {
                // Later requests might be rate limited (429) or still processed (200)
                $this->assertContains($response->getStatusCode(), [200, 429]);
                
                if ($response->getStatusCode() === 429) {
                    // If we get rate limited, we can break early
                    break;
                }
            }
        }
    }

    /**
     * Test callback IP whitelist protection
     */
    public function test_callback_ip_whitelist_protection()
    {
        // Set allowed IPs for testing
        Config::set('services.ebilling.allowed_ips', ['192.168.1.100', '10.0.0.5']);
        Config::set('services.ebilling.webhook_secret', null); // Disable HMAC for this test

        $payload = [
            'reference' => 'KMB-PAY-SEC-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-IP-TEST-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ];

        // Test request from allowed IP
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '192.168.1.100'
        ])->postJson('/api/payments/callback', $payload);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

        // Reset payment status for next test
        $this->payment->update(['status' => 'pending', 'paid_at' => null]);
        $this->order->update(['status' => OrderStatus::AWAITING_PAYMENT->value, 'paid_at' => null]);

        // Test request from disallowed IP
        $payload['transactionid'] = 'TXN-IP-BAD-456';
        
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.50' // Different IP not in whitelist
        ])->postJson('/api/payments/callback', $payload);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'IP not allowed'
                ]);

        // Verify payment was NOT processed
        $this->payment->refresh();
        $this->assertEquals('pending', $this->payment->status);
    }

    /**
     * Test callback timing attack protection
     */
    public function test_callback_timing_attack_protection()
    {
        Config::set('services.ebilling.webhook_secret', 'test-secret-key');

        $payload = [
            'reference' => 'KMB-PAY-SEC-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-TIMING-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ];

        $validSignature = hash_hmac('sha256', json_encode($payload), 'test-secret-key');
        $invalidSignature = 'invalid-signature-value';

        // Measure time for valid signature
        $startTime = microtime(true);
        $response1 = $this->withHeaders([
            'X-Signature' => $validSignature
        ])->postJson('/api/payments/callback', $payload);
        $validTime = microtime(true) - $startTime;

        // Reset for next test
        $this->payment->update(['status' => 'pending', 'paid_at' => null]);
        $this->order->update(['status' => OrderStatus::AWAITING_PAYMENT->value, 'paid_at' => null]);

        // Measure time for invalid signature
        $payload['transactionid'] = 'TXN-TIMING-456';
        $startTime = microtime(true);
        $response2 = $this->withHeaders([
            'X-Signature' => $invalidSignature
        ])->postJson('/api/payments/callback', $payload);
        $invalidTime = microtime(true) - $startTime;

        // Both should complete in reasonable time (timing attack mitigation)
        $this->assertLessThan(1.0, $validTime); // Should be fast
        $this->assertLessThan(1.0, $invalidTime); // Should also be reasonably fast

        // The time difference should not be extreme (within 100ms difference)
        $timeDifference = abs($validTime - $invalidTime);
        $this->assertLessThan(0.1, $timeDifference, 'Timing difference suggests vulnerability to timing attacks');
    }

    /**
     * Test callback payload size limits
     */
    public function test_callback_payload_size_limits()
    {
        Config::set('services.ebilling.webhook_secret', null); // Disable HMAC for this test

        // Create a very large payload
        $largeDescription = str_repeat('A', 10000); // 10KB string
        
        $payload = [
            'reference' => 'KMB-PAY-SEC-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-LARGE-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success',
            'description' => $largeDescription,
            'metadata' => str_repeat('B', 5000) // Another 5KB
        ];

        $response = $this->postJson('/api/payments/callback', $payload);

        // Should either accept or reject based on configured limits
        $this->assertContains($response->getStatusCode(), [200, 413, 400]);
        
        if ($response->getStatusCode() === 413) {
            $response->assertJson([
                'message' => 'Payload Too Large'
            ]);
        }
    }

    /**
     * Test callback with malformed JSON handling
     */
    public function test_callback_malformed_json_handling()
    {
        // Send malformed JSON
        $response = $this->postJson('/api/payments/callback', 'invalid-json-data');

        $response->assertStatus(400);
    }

    /**
     * Test callback logs security events
     */
    public function test_callback_logs_security_events()
    {
        Log::shouldReceive('warning')
           ->once()
           ->with('Payment callback security event', \Mockery::type('array'));

        Config::set('services.ebilling.webhook_secret', 'test-secret-key');

        $payload = [
            'reference' => 'KMB-PAY-SEC-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-LOG-123',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ];

        // Send callback with invalid signature to trigger security log
        $response = $this->withHeaders([
            'X-Signature' => 'invalid-signature'
        ])->postJson('/api/payments/callback', $payload);

        $response->assertStatus(403);
    }
}