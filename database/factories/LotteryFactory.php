<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lottery>
 */
class LotteryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxTickets = $this->faker->numberBetween(50, 1000);
        $soldTickets = $this->faker->numberBetween(0, $maxTickets);
        
        return [
            'product_id' => Product::factory(),
            'lottery_number' => 'LOT-' . strtoupper(uniqid()),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'ticket_price' => $this->faker->randomFloat(2, 500, 5000),
            'max_tickets' => $maxTickets,
            'sold_tickets' => $soldTickets,
            'status' => $this->faker->randomElement(['active', 'completed', 'cancelled']),
            'draw_date' => $this->faker->optional(0.8)->dateTimeBetween('+1 day', '+30 days'),
            'winner_ticket_number' => $this->faker->optional(0.1)->bothify('TICKET-########'),
            'winner_user_id' => $this->faker->optional(0.1)->numberBetween(1, 100),
            'prize_description' => $this->faker->optional()->sentence(),
            'rules' => $this->faker->optional()->paragraphs(2, true),
            'meta' => $this->faker->optional(0.3)->randomElement([
                ['jackpot' => true, 'multiplier' => 2.0],
                ['featured' => true, 'category' => 'special'],
                ['promotional' => true, 'discount' => 10],
            ]),
        ];
    }

    /**
     * Create an active lottery
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'draw_date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'winner_ticket_number' => null,
            'winner_user_id' => null,
        ]);
    }

    /**
     * Create a completed lottery
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'draw_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'winner_ticket_number' => 'TICKET-' . strtoupper(uniqid()),
            'winner_user_id' => $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * Create a cancelled lottery
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'winner_ticket_number' => null,
            'winner_user_id' => null,
        ]);
    }

    /**
     * Create a lottery with specific ticket configuration
     */
    public function withTickets(int $maxTickets, int $soldTickets = null): static
    {
        return $this->state(fn (array $attributes) => [
            'max_tickets' => $maxTickets,
            'sold_tickets' => $soldTickets ?? $this->faker->numberBetween(0, $maxTickets),
        ]);
    }
}