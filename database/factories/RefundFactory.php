<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Refund>
 */
class RefundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'refund_number' => 'REF-' . strtoupper(\Illuminate\Support\Str::random(16)),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'payment_id' => Payment::factory(),
            'amount' => $this->faker->randomFloat(2, 500, 50000),
            'currency' => 'XAF',
            'reason' => 'lottery_cancelled',
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved', 'approved_at' => now()]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed', 'completed_at' => now()]);
    }
}
