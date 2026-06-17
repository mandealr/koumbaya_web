<?php

namespace Tests\Feature;

use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LotteryFlowTest extends TestCase
{
    use RefreshDatabase;

    private function merchant(): User
    {
        $role = Role::firstOrCreate(['name' => 'Business Enterprise'], ['description' => 'pro']);
        $user = User::factory()->create(['verified_at' => now(), 'user_type_id' => 1]);
        $user->roles()->attach($role->id);

        return $user;
    }

    /** Tombola éligible (date atteinte, participants suffisants) détenue par $merchant. */
    private function eligibleLottery(User $merchant, int $participants = 5): Lottery
    {
        $product = Product::factory()->lottery()->create([
            'merchant_id' => $merchant->id,
            'meta' => ['min_participants' => 3],
        ]);

        $lottery = Lottery::factory()->drawable()->create([
            'product_id' => $product->id,
            'max_tickets' => max($participants, 10),
            'sold_tickets' => $participants,
        ]);

        LotteryTicket::factory()->count($participants)->create(['lottery_id' => $lottery->id]);

        return $lottery;
    }

    public function test_public_can_list_and_view_lotteries(): void
    {
        $lottery = Lottery::factory()->create();

        $this->getJson('/api/lotteries')->assertStatus(200);
        $this->getJson('/api/lotteries/' . $lottery->id)->assertStatus(200);
    }

    public function test_owner_can_draw_eligible_lottery(): void
    {
        $merchant = $this->merchant();
        $lottery = $this->eligibleLottery($merchant);
        $token = $merchant->createAuthToken('t');

        $this->withToken($token)
            ->postJson('/api/lotteries/' . $lottery->id . '/draw')
            ->assertStatus(200);

        $lottery->refresh();
        $this->assertEquals('completed', $lottery->status);
        $this->assertNotNull($lottery->winning_ticket_number);
        $this->assertDatabaseHas('draw_histories', ['lottery_id' => $lottery->id]);
    }

    public function test_stranger_cannot_draw_someone_elses_lottery(): void
    {
        $owner = $this->merchant();
        $lottery = $this->eligibleLottery($owner);

        $this->app['auth']->forgetGuards();

        $stranger = $this->merchant();
        $this->withToken($stranger->createAuthToken('t'))
            ->postJson('/api/lotteries/' . $lottery->id . '/draw')
            ->assertStatus(403);

        $this->assertEquals('active', $lottery->fresh()->status);
    }
}
