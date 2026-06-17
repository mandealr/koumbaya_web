<?php

namespace App\Policies;

use App\Models\Lottery;
use App\Models\User;

/**
 * Autorisation centralisée sur les tombolas : seul le marchand propriétaire du
 * produit associé (ou un administrateur) peut la gérer / déclencher le tirage.
 */
class LotteryPolicy
{
    public function update(User $user, Lottery $lottery): bool
    {
        return $this->owns($user, $lottery) || $user->isAdmin();
    }

    public function draw(User $user, Lottery $lottery): bool
    {
        return $this->owns($user, $lottery) || $user->isAdmin();
    }

    public function delete(User $user, Lottery $lottery): bool
    {
        return $this->owns($user, $lottery) || $user->isAdmin();
    }

    private function owns(User $user, Lottery $lottery): bool
    {
        return (int) optional($lottery->product)->merchant_id === (int) $user->id;
    }
}
