<?php

namespace Tests\Feature;

use App\Models\DrawHistory;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie que la commande cron lottery:draw effectue bien un tirage VÉRIFIABLE
 * via LotteryDrawService (et non l'ancien algorithme inline) et enregistre
 * l'historique dans draw_histories.
 */
class LotteryDrawCommandTest extends TestCase
{
    use RefreshDatabase;

    private function makeEligibleLottery(int $participants, int $minParticipants): Lottery
    {
        $merchant = User::factory()->create();

        $product = Product::create([
            'name' => 'Produit Tombola',
            'description' => 'Description test',
            'price' => 100000,
            'currency' => 'XAF',
            'category_id' => 1,
            'merchant_id' => $merchant->id,
            'sale_mode' => 'lottery',
            'meta' => ['min_participants' => $minParticipants],
        ]);

        $lottery = Lottery::create([
            'lottery_number' => 'LOT-TEST-' . uniqid(),
            'title' => 'Tombola test',
            'description' => 'Description',
            'product_id' => $product->id,
            'ticket_price' => 1000,
            'currency' => 'XAF',
            'max_tickets' => max($participants, 5),
            'sold_tickets' => $participants,
            'draw_date' => now()->subDay(),
            'status' => 'active',
        ]);

        for ($i = 0; $i < $participants; $i++) {
            $user = User::factory()->create();
            LotteryTicket::create([
                'ticket_number' => 'T-' . $lottery->id . '-' . $i,
                'lottery_id' => $lottery->id,
                'user_id' => $user->id,
                'price' => 1000,
                'currency' => 'XAF',
                'status' => 'paid',
                'purchased_at' => now(),
            ]);
        }

        return $lottery;
    }

    public function test_command_draws_verifiable_winner_and_records_history(): void
    {
        $lottery = $this->makeEligibleLottery(participants: 5, minParticipants: 3);

        $this->artisan('lottery:draw', ['--lottery' => [$lottery->id]])
            ->assertExitCode(0);

        $lottery->refresh();

        $this->assertEquals('completed', $lottery->status);
        $this->assertNotNull($lottery->winning_ticket_number);
        $this->assertNotNull($lottery->winner_user_id);

        // Historique vérifiable enregistré (preuve que le service a été utilisé)
        $this->assertDatabaseHas('draw_histories', [
            'lottery_id' => $lottery->id,
            'method' => 'auto',
            'winning_ticket_number' => $lottery->winning_ticket_number,
        ]);

        // Exactement un ticket gagnant
        $this->assertEquals(
            1,
            LotteryTicket::where('lottery_id', $lottery->id)->where('is_winner', true)->count()
        );
    }

    public function test_command_skips_lottery_without_enough_participants(): void
    {
        // 2 participants pour un minimum de 3 → pas de tirage
        $lottery = $this->makeEligibleLottery(participants: 2, minParticipants: 3);

        $this->artisan('lottery:draw', ['--lottery' => [$lottery->id]])
            ->assertExitCode(0);

        $lottery->refresh();

        $this->assertEquals('active', $lottery->status);
        $this->assertNull($lottery->winning_ticket_number);
        $this->assertDatabaseMissing('draw_histories', ['lottery_id' => $lottery->id]);
    }
}
