<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Lottery;
use App\Models\Product;
use App\Models\LotteryTicket;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class PaymentCallbackOrderStatusTest extends TestCase
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

    /** @test */
    public function payment_callback_marks_lottery_order_as_paid()
    {
        // Create test data
        $user = User::factory()->create();
        $lottery = Lottery::factory()->create(['status' => 'active']);
        
        $order = Order::create([
            'order_number' => 'ORD-' . uniqid(),
            'user_id' => $user->id,
            'type' => 'lottery',
            'lottery_id' => $lottery->id,
            'total_amount' => 1000.00,
            'currency' => 'XAF',
            'status' => OrderStatus::PENDING->value,
        ]);
        
        $payment = Payment::create([
            'reference' => 'TEST-LOTTERY-REF-001',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 1000.00,
            'status' => PaymentStatus::PENDING->value,
            'ebilling_id' => 'BILL-LOTTERY-123',
        ]);

        // Create some lottery tickets
        LotteryTicket::create([
            'lottery_id' => $lottery->id,
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'ticket_number' => 'TKT-001',
            'price' => 1000.00,
            'currency' => 'XAF',
            'status' => 'reserved',
        ]);

        // Test successful callback
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-LOTTERY-REF-001',
            'amount' => 1000.00,
            'transactionid' => 'TXN-SUCCESS-LOTTERY-456',
            'paymentsystem' => 'airtelmoney',
            'status' => 'success'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Callback processed successfully'
        ]);

        // Verify payment status
        $payment->refresh();
        $this->assertEquals(PaymentStatus::PAID->value, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('airtelmoney', $payment->payment_method);
        $this->assertEquals('TXN-SUCCESS-LOTTERY-456', $payment->transaction_id);

        // Verify order status - THIS IS THE KEY TEST
        $order->refresh();
        $this->assertEquals(OrderStatus::PAID->value, $order->status, 'Order status should be "paid" after successful payment callback');
        $this->assertNotNull($order->paid_at);
        $this->assertEquals('TXN-SUCCESS-LOTTERY-456', $order->payment_reference);

        // Verify lottery tickets status
        $tickets = LotteryTicket::where('payment_id', $payment->id)->get();
        foreach ($tickets as $ticket) {
            $this->assertEquals('paid', $ticket->status);
            $this->assertNotNull($ticket->confirmed_at);
        }
    }

    /** @test */
    public function payment_callback_marks_direct_product_order_as_paid()
    {
        // Create test data
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 5000.00]);
        
        $order = Order::create([
            'order_number' => 'ORD-' . uniqid(),
            'user_id' => $user->id,
            'type' => 'direct',
            'product_id' => $product->id,
            'total_amount' => 5000.00,
            'currency' => 'XAF',
            'status' => OrderStatus::PENDING->value,
        ]);
        
        $payment = Payment::create([
            'reference' => 'TEST-DIRECT-REF-001',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 5000.00,
            'status' => PaymentStatus::PENDING->value,
            'ebilling_id' => 'BILL-DIRECT-123',
        ]);

        // Test successful callback
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-DIRECT-REF-001',
            'amount' => 5000.00,
            'transactionid' => 'TXN-SUCCESS-DIRECT-789',
            'paymentsystem' => 'moovmoney4',
            'status' => 'success'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Callback processed successfully'
        ]);

        // Verify payment status
        $payment->refresh();
        $this->assertEquals(PaymentStatus::PAID->value, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('moovmoney4', $payment->payment_method);
        $this->assertEquals('TXN-SUCCESS-DIRECT-789', $payment->transaction_id);

        // Verify order status - THIS IS THE KEY TEST
        $order->refresh();
        $this->assertEquals(OrderStatus::PAID->value, $order->status, 'Direct product order status should be "paid" after successful payment callback');
        $this->assertNotNull($order->paid_at);
        $this->assertEquals('TXN-SUCCESS-DIRECT-789', $order->payment_reference);
    }

    /** @test */
    public function payment_callback_handles_failed_payment_correctly()
    {
        // Create test data
        $user = User::factory()->create();
        $lottery = Lottery::factory()->create(['status' => 'active']);
        
        $order = Order::create([
            'order_number' => 'ORD-' . uniqid(),
            'user_id' => $user->id,
            'type' => 'lottery',
            'lottery_id' => $lottery->id,
            'total_amount' => 1000.00,
            'currency' => 'XAF',
            'status' => OrderStatus::PENDING->value,
        ]);
        
        $payment = Payment::create([
            'reference' => 'TEST-FAILED-REF-001',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 1000.00,
            'status' => PaymentStatus::PENDING->value,
            'ebilling_id' => 'BILL-FAILED-123',
        ]);

        // Test failed callback
        $response = $this->postJson('/api/payments/callback', [
            'reference' => 'TEST-FAILED-REF-001',
            'amount' => 1000.00,
            'transactionid' => 'TXN-FAILED-456',
            'paymentsystem' => 'airtelmoney',
            'status' => 'failed'
        ]);

        $response->assertStatus(200);

        // Verify payment status
        $payment->refresh();
        $this->assertEquals(PaymentStatus::FAILED->value, $payment->status);

        // Verify order status
        $order->refresh();
        $this->assertEquals(OrderStatus::FAILED->value, $order->status, 'Order status should be "failed" after failed payment callback');
    }
}