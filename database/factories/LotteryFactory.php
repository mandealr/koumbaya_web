<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lottery>
 */
class LotteryFactory extends Factory
{
    public function definition(): array
    {
        $maxTickets = $this->faker->numberBetween(50, 500);

        return [
            'product_id' => Product::factory()->lottery(),
            'lottery_number' => 'LOT-' . strtoupper(uniqid()),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'ticket_price' => $this->faker->randomFloat(2, 500, 5000),
            'currency' => 'XAF',
            'max_tickets' => $maxTickets,
            'sold_tickets' => 0,
            'status' => 'active',
            'draw_date' => now()->addDays(7),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'draw_date' => now()->addDays(7),
            'winning_ticket_number' => null,
            'winner_user_id' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'draw_date' => now()->subDay(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }

    /**
     * Tombola éligible au tirage (date atteinte).
     */
    public function drawable(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'draw_date' => now()->subDay(),
        ]);
    }
}
