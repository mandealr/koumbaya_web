<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\LotteryTicket;
use Illuminate\Support\Facades\DB;

class BackfillOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:backfill {--dry-run : Show what would be created without actually creating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing transactions to the new orders table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 Running in dry-run mode - no data will be created');
        }

        $this->info('🚀 Starting orders backfill from transactions...');

        // Get all transactions that haven't been migrated yet
        $existingOrderTransactionIds = Order::whereNotNull('meta->transaction_id')
            ->pluck('meta->transaction_id')
            ->toArray();

        $transactions = Payment::whereNotIn('id', $existingOrderTransactionIds)
            ->with(['lotteryTickets'])
            ->orderBy('created_at')
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('✅ No transactions found to migrate');
            return;
        }

        $this->info("📊 Found {$transactions->count()} transactions to migrate");

        $createdCount = 0;
        $bar = $this->output->createProgressBar($transactions->count());
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($transactions as $transaction) {
                $orderData = $this->buildOrderData($transaction);
                
                if ($isDryRun) {
                    $this->line("\n🔍 Would create order: " . json_encode($orderData, JSON_PRETTY_PRINT));
                } else {
                    $order = Order::create($orderData);
                    // Link the transaction to the newly created order
                    $transaction->update(['order_id' => $order->id]);
                    $createdCount++;
                }
                
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            if ($isDryRun) {
                $this->info("✅ Dry run completed - would create {$transactions->count()} orders");
                DB::rollBack();
            } else {
                DB::commit();
                $this->info("✅ Successfully created {$createdCount} orders from transactions");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error during migration: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Build order data from transaction
     */
    private function buildOrderData(Transaction $transaction): array
    {
        // Map transaction type to order type
        $orderType = $this->mapTransactionTypeToOrderType($transaction->type);
        
        // Map transaction status to order status
        $orderStatus = $this->mapTransactionStatusToOrderStatus($transaction->status);
        
        // Generate deterministic order number
        $orderNumber = $this->generateOrderNumber($transaction);
        
        // Build meta data with source transaction info
        $meta = [
            'transaction_id' => $transaction->id,
            'source' => 'backfill',
            'original_type' => $transaction->type,
            'original_status' => $transaction->status,
        ];

        // Add lottery ticket IDs if this is a lottery transaction
        if ($orderType === 'lottery' && $transaction->lotteryTickets->isNotEmpty()) {
            $meta['lottery_ticket_ids'] = $transaction->lotteryTickets->pluck('id')->toArray();
        }

        return [
            'order_number' => $orderNumber,
            'user_id' => $transaction->user_id,
            'type' => $orderType,
            'product_id' => $orderType === 'direct' ? $transaction->product_id : null,
            'lottery_id' => $orderType === 'lottery' ? $transaction->lottery_id : null,
            'total_amount' => $transaction->amount,
            'currency' => $transaction->currency ?? 'XAF',
            'status' => $orderStatus,
            'payment_reference' => $transaction->reference ?? $transaction->transaction_id,
            'paid_at' => $this->getPaidAtDate($transaction, $orderStatus),
            'fulfilled_at' => $this->getFulfilledAtDate($transaction, $orderStatus),
            'meta' => $meta,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
        ];
    }

    /**
     * Map transaction type to order type
     */
    private function mapTransactionTypeToOrderType(string $transactionType): string
    {
        return match ($transactionType) {
            'ticket_purchase', 'lottery_ticket' => 'lottery',
            'direct_purchase' => 'direct',
            default => 'direct', // Default fallback
        };
    }

    /**
     * Map transaction status to order status
     */
    private function mapTransactionStatusToOrderStatus(string $transactionStatus): string
    {
        return match ($transactionStatus) {
            'pending', 'payment_initiated' => 'awaiting_payment',
            'paid', 'completed' => 'paid',
            'failed' => 'failed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            default => 'pending', // Default fallback
        };
    }

    /**
     * Generate deterministic order number
     */
    private function generateOrderNumber(Transaction $transaction): string
    {
        $date = $transaction->created_at->format('Ymd');
        $userId = str_pad($transaction->user_id, 4, '0', STR_PAD_LEFT);
        $transactionId = str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
        
        return "ORD-{$date}-{$userId}-{$transactionId}";
    }

    /**
     * Get paid_at date based on transaction data and order status
     */
    private function getPaidAtDate(Transaction $transaction, string $orderStatus): ?string
    {
        if ($orderStatus === 'paid') {
            return $transaction->paid_at ?? $transaction->completed_at ?? $transaction->updated_at;
        }
        
        return null;
    }

    /**
     * Get fulfilled_at date based on transaction data and order status
     */
    private function getFulfilledAtDate(Transaction $transaction, string $orderStatus): ?string
    {
        if ($orderStatus === 'paid' && $transaction->completed_at) {
            return $transaction->completed_at;
        }
        
        return null;
    }
}
