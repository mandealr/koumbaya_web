<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShapPayoutService;
use Illuminate\Support\Facades\Log;

class TestShapIntegration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shap:test 
                            {--balance : Test balance retrieval}
                            {--payout : Test payout creation}
                            {--phone=074123456 : Phone number for test payout}
                            {--amount=1000 : Amount for test payout}';

    /**
     * The console command description.
     */
    protected $description = 'Test SHAP API integration for refunds';

    protected $shapService;

    public function __construct(ShapPayoutService $shapService)
    {
        parent::__construct();
        $this->shapService = $shapService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing SHAP API Integration');
        $this->newLine();

        // Test authentication
        $this->info('1. Testing Authentication...');
        if ($this->shapService->authenticate()) {
            $this->info('✅ Authentication successful');
        } else {
            $this->error('❌ Authentication failed');
            return 1;
        }

        // Test balance retrieval
        if ($this->option('balance') || !$this->option('payout')) {
            $this->newLine();
            $this->info('2. Testing Balance Retrieval...');
            try {
                $balances = $this->shapService->getBalance();
                $this->info('✅ Balance retrieved successfully');
                
                $this->table(
                    ['Operator', 'Name', 'Balance (XAF)', 'Last Updated'],
                    collect($balances)->map(function ($balance) {
                        return [
                            $balance['payment_system_name'],
                            $balance['payment_system_displayed_name'],
                            number_format($balance['amount'], 0, ',', ' '),
                            $balance['last_updated']
                        ];
                    })->toArray()
                );
            } catch (\Exception $e) {
                $this->error('❌ Balance retrieval failed: ' . $e->getMessage());
            }
        }

        // Test payout creation
        if ($this->option('payout')) {
            $this->newLine();
            $this->info('3. Testing Payout Creation...');
            
            $phone = $this->option('phone');
            $amount = (float) $this->option('amount');
            
            $this->warn("⚠️  This will create a REAL payout!");
            $this->info("Phone: {$phone}");
            $this->info("Amount: {$amount} XAF");
            
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Payout test cancelled.');
                return 0;
            }

            try {
                // Detect operator
                $operator = $this->shapService->detectOperatorFromPhone($phone);
                $this->info("Detected operator: {$operator}");

                // Validate data
                $errors = $this->shapService->validatePayoutData($phone, $amount);
                if (!empty($errors)) {
                    $this->error('❌ Validation errors:');
                    foreach ($errors as $error) {
                        $this->error("  - {$error}");
                    }
                    return 1;
                }

                // Check balance
                $balance = $this->shapService->getOperatorBalance($operator);
                $this->info("Operator balance: " . number_format($balance, 0, ',', ' ') . " XAF");

                if ($balance < $amount) {
                    $this->error('❌ Insufficient balance');
                    return 1;
                }

                // Create payout
                $reference = 'TEST-' . time();
                $response = $this->shapService->createPayout(
                    $operator,
                    $phone,
                    $amount,
                    $reference,
                    'refund'
                );

                $this->info('✅ Payout created successfully');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Payout ID', $response['payout_id']],
                        ['Transaction ID', $response['transaction_id']],
                        ['Status', $response['state']],
                        ['Amount', $response['amount'] . ' ' . $response['currency']],
                        ['Created At', $response['created_at']],
                    ]
                );

            } catch (\Exception $e) {
                $this->error('❌ Payout creation failed: ' . $e->getMessage());
                Log::error('SHAP test payout failed', [
                    'phone' => $phone,
                    'amount' => $amount,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
        $this->info('🏁 SHAP integration test completed');
        
        return 0;
    }
}