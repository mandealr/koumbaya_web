<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created()
    {
        $user = User::factory()->create();
        
        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'user_id' => $user->id,
            'type' => Order::TYPE_DIRECT,
            'total_amount' => 10000,
            'status' => Order::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => 'ORD-TEST-001',
            'user_id' => $user->id,
            'type' => 'direct',
            'total_amount' => 10000.00,
            'status' => 'pending',
            'currency' => 'XAF',
        ]);
    }

    public function test_order_number_is_unique()
    {
        $orderNumber = Order::generateOrderNumber();
        
        $this->assertStringStartsWith('ORD-', $orderNumber);
        $this->assertTrue(strlen($orderNumber) > 10);
        
        // Test uniqueness
        $anotherOrderNumber = Order::generateOrderNumber();
        $this->assertNotEquals($orderNumber, $anotherOrderNumber);
    }

    public function test_paid_scope_works()
    {
        $user = User::factory()->create();
        
        Order::factory()->paid()->create(['user_id' => $user->id]);
        Order::factory()->pending()->create(['user_id' => $user->id]);
        Order::factory()->create(['status' => 'failed', 'user_id' => $user->id]);

        $this->assertEquals(1, Order::paid()->count());
    }

    public function test_pending_scope_works()
    {
        $user = User::factory()->create();
        
        Order::factory()->pending()->create(['user_id' => $user->id]);
        Order::factory()->pending()->create(['user_id' => $user->id]);
        Order::factory()->paid()->create(['user_id' => $user->id]);

        $this->assertEquals(2, Order::pending()->count());
    }

    public function test_by_user_scope_works()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Order::factory()->count(3)->create(['user_id' => $user1->id]);
        Order::factory()->count(2)->create(['user_id' => $user2->id]);

        $this->assertEquals(3, Order::byUser($user1->id)->count());
        $this->assertEquals(2, Order::byUser($user2->id)->count());
    }

    public function test_is_paid_accessor()
    {
        $user = User::factory()->create();
        
        $paidOrder = Order::factory()->paid()->create(['user_id' => $user->id]);
        $pendingOrder = Order::factory()->pending()->create(['user_id' => $user->id]);

        $this->assertTrue($paidOrder->is_paid);
        $this->assertFalse($pendingOrder->is_paid);
    }

    public function test_mark_as_paid_method()
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create(['user_id' => $user->id]);

        $this->assertFalse($order->is_paid);
        $this->assertNull($order->paid_at);
        $this->assertNull($order->payment_reference);

        $order->markAsPaid('PAY-123456');

        $this->assertTrue($order->fresh()->is_paid);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertEquals('PAY-123456', $order->fresh()->payment_reference);
    }

    public function test_mark_as_fulfilled_method()
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->create(['user_id' => $user->id]);

        $this->assertNull($order->fulfilled_at);

        $order->markAsFulfilled();

        $this->assertEquals(Order::STATUS_FULFILLED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->fulfilled_at);
    }

    public function test_can_be_cancelled_method()
    {
        $user = User::factory()->create();
        
        $pendingOrder = Order::factory()->pending()->create(['user_id' => $user->id]);
        $paidOrder = Order::factory()->paid()->create(['user_id' => $user->id]);

        $this->assertTrue($pendingOrder->canBeCancelled());
        $this->assertFalse($paidOrder->canBeCancelled());
    }

    public function test_can_be_fulfilled_method()
    {
        $user = User::factory()->create();
        
        $paidOrder = Order::factory()->paid()->create(['user_id' => $user->id]);
        $pendingOrder = Order::factory()->pending()->create(['user_id' => $user->id]);

        $this->assertTrue($paidOrder->canBeFulfilled());
        $this->assertFalse($pendingOrder->canBeFulfilled());
    }

    public function test_user_relationship()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }
}