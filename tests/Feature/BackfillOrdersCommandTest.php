<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\LotteryTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BackfillOrdersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_command_creates_orders_from_transactions()
    {
        // Create test data
        $user = User::factory()->create();
        
        $lotteryTransaction = Transaction::create([
            'reference' => 'TXN-LOTTERY-001',
            'user_id' => $user->id,
            'type' => 'ticket_purchase',
            'amount' => 1000.00,
            'currency' => 'XAF',
            'status' => 'completed',
            'lottery_id' => 1,
            'quantity' => 2,
            'created_at' => now()->subDays(5),
        ]);

        $directTransaction = Transaction::create([
            'reference' => 'TXN-DIRECT-001',
            'user_id' => $user->id,
            'type' => 'direct_purchase',
            'amount' => 2500.00,
            'currency' => 'XAF',
            'status' => 'paid',
            'product_id' => 1,
            'created_at' => now()->subDays(3),
        ]);

        // Run the command
        $this->artisan('orders:backfill')
            ->expectsOutput('🚀 Starting orders backfill from transactions...')
            ->expectsOutputToContain('Found 2 transactions to migrate')
            ->expectsOutput('✅ Successfully created 2 orders from transactions')
            ->assertExitCode(0);

        // Verify orders were created
        $this->assertDatabaseCount('orders', 2);

        // Check lottery order
        $lotteryOrder = Order::where('type', 'lottery')->first();
        $this->assertNotNull($lotteryOrder);
        $this->assertEquals($user->id, $lotteryOrder->user_id);
        $this->assertEquals(1000.00, $lotteryOrder->total_amount);
        $this->assertEquals('paid', $lotteryOrder->status);
        $this->assertEquals(1, $lotteryOrder->lottery_id);
        $this->assertNull($lotteryOrder->product_id);
        $this->assertEquals($lotteryTransaction->id, $lotteryOrder->meta['transaction_id']);
        $this->assertEquals('ticket_purchase', $lotteryOrder->meta['original_type']);
        $this->assertStringStartsWith('ORD-', $lotteryOrder->order_number);

        // Check direct order
        $directOrder = Order::where('type', 'direct')->first();
        $this->assertNotNull($directOrder);
        $this->assertEquals($user->id, $directOrder->user_id);
        $this->assertEquals(2500.00, $directOrder->total_amount);
        $this->assertEquals('paid', $directOrder->status);
        $this->assertEquals(1, $directOrder->product_id);
        $this->assertNull($directOrder->lottery_id);
        $this->assertEquals($directTransaction->id, $directOrder->meta['transaction_id']);
        $this->assertEquals('direct_purchase', $directOrder->meta['original_type']);
    }

    public function test_backfill_command_dry_run_mode()
    {
        $user = User::factory()->create();
        
        Transaction::create([
            'reference' => 'TXN-TEST-001',
            'user_id' => $user->id,
            'type' => 'ticket_purchase',
            'amount' => 1000.00,
            'currency' => 'XAF',
            'status' => 'completed',
        ]);

        // Run in dry-run mode
        $this->artisan('orders:backfill --dry-run')
            ->expectsOutput('🔍 Running in dry-run mode - no data will be created')
            ->expectsOutputToContain('Found 1 transactions to migrate')
            ->expectsOutput('✅ Dry run completed - would create 1 orders')
            ->assertExitCode(0);

        // Verify no orders were actually created
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_backfill_command_skips_already_migrated_transactions()
    {
        $user = User::factory()->create();
        
        $transaction = Transaction::create([
            'reference' => 'TXN-EXISTING-001',
            'user_id' => $user->id,
            'type' => 'direct_purchase',
            'amount' => 1500.00,
            'currency' => 'XAF',
            'status' => 'paid',
        ]);

        // Create an order that references this transaction
        Order::create([
            'order_number' => 'ORD-EXISTING-001',
            'user_id' => $user->id,
            'type' => 'direct',
            'total_amount' => 1500.00,
            'status' => 'paid',
            'meta' => ['transaction_id' => $transaction->id],
        ]);

        // Run the command
        $this->artisan('orders:backfill')
            ->expectsOutput('✅ No transactions found to migrate')
            ->assertExitCode(0);

        // Should still have only 1 order
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_order_number_generation_is_deterministic()
    {
        $user = User::factory()->create();
        
        $transaction = Transaction::create([
            'reference' => 'TXN-DETERMINISTIC-001',
            'user_id' => $user->id,
            'type' => 'direct_purchase',
            'amount' => 1000.00,
            'currency' => 'XAF',
            'status' => 'paid',
            'created_at' => '2024-03-15 10:30:00',
        ]);

        $this->artisan('orders:backfill');

        $order = Order::first();
        $expectedOrderNumber = 'ORD-20240315-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
        
        $this->assertEquals($expectedOrderNumber, $order->order_number);
    }
}