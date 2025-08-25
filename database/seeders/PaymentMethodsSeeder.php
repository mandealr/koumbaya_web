<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'method_id' => 'ebilling_airtel',
                'name' => 'Airtel Money',
                'gateway' => 'ebilling',
                'active' => true,
                'config' => json_encode([
                    'gateway_type' => 'mobile_money',
                    'operator' => 'airtel',
                    'currency' => 'XAF'
                ]),
                'sort_order' => 1
            ],
            [
                'method_id' => 'ebilling_moov',
                'name' => 'Moov Money',
                'gateway' => 'ebilling',
                'active' => true,
                'config' => json_encode([
                    'gateway_type' => 'mobile_money',
                    'operator' => 'moov',
                    'currency' => 'XAF'
                ]),
                'sort_order' => 2
            ],
            [
                'method_id' => 'flutterwave',
                'name' => 'Flutterwave',
                'gateway' => 'flutterwave',
                'active' => true,
                'config' => json_encode([
                    'gateway_type' => 'card_payment',
                    'supports_mobile_money' => true,
                    'currency' => 'XAF'
                ]),
                'sort_order' => 3
            ],
            [
                'method_id' => 'paystack',
                'name' => 'Paystack',
                'gateway' => 'paystack',
                'active' => true,
                'config' => json_encode([
                    'gateway_type' => 'card_payment',
                    'supports_bank_transfer' => true,
                    'currency' => 'XAF'
                ]),
                'sort_order' => 4
            ],
            [
                'method_id' => 'bank_transfer',
                'name' => 'Virement Bancaire',
                'gateway' => 'manual',
                'active' => true,
                'config' => json_encode([
                    'gateway_type' => 'bank_transfer',
                    'requires_manual_verification' => true,
                    'currency' => 'XAF'
                ]),
                'sort_order' => 5
            ]
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['method_id' => $method['method_id']],
                $method
            );
        }
    }
}