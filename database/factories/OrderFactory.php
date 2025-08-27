<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use App\Models\Lottery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['lottery', 'direct']);
        
        return [
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'user_id' => 1,
            'type' => $type,
            'product_id' => $type === 'direct' ? 1 : null,
            'lottery_id' => $type === 'lottery' ? 1 : null,
            'total_amount' => $this->faker->randomFloat(2, 1000, 50000),
            'currency' => 'XAF',
            'status' => $this->faker->randomElement([
                'pending',
                'awaiting_payment',
                'paid',
                'failed',
                'cancelled',
                'fulfilled',
                'refunded'
            ]),
            'payment_reference' => $this->faker->optional()->bothify('PAY-########'),
            'paid_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'fulfilled_at' => $this->faker->optional(0.5)->dateTimeBetween('-15 days', 'now'),
            'meta' => $this->faker->optional(0.3)->randomElement([
                ['source' => 'web', 'device' => 'desktop'],
                ['source' => 'mobile', 'device' => 'android'],
                ['promo_code' => 'WELCOME10', 'discount' => 10],
                ['referrer' => 'facebook', 'campaign' => 'summer2024'],
            ]),
        ];
    }

    /**
     * Create a paid order
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'payment_reference' => 'PAY-' . strtoupper(uniqid()),
        ]);
    }

    /**
     * Create a pending order
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
            'payment_reference' => null,
        ]);
    }

    /**
     * Create a lottery order
     */
    public function lottery(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'lottery',
            'lottery_id' => 1,
            'product_id' => null,
        ]);
    }

    /**
     * Create a direct product order
     */
    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'direct',
            'product_id' => 1,
            'lottery_id' => null,
        ]);
    }

    /**
     * Create a fulfilled order
     */
    public function fulfilled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'fulfilled',
            'paid_at' => $this->faker->dateTimeBetween('-30 days', '-5 days'),
            'fulfilled_at' => $this->faker->dateTimeBetween('-5 days', 'now'),
            'payment_reference' => 'PAY-' . strtoupper(uniqid()),
        ]);
    }
}
