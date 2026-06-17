<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Autorisation centralisée sur les produits : seul le marchand propriétaire
 * (ou un administrateur) peut modifier le produit / créer sa tombola.
 */
class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $this->owns($user, $product) || $user->isAdmin();
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->owns($user, $product) || $user->isAdmin();
    }

    public function createLottery(User $user, Product $product): bool
    {
        return $this->owns($user, $product) || $user->isAdmin();
    }

    private function owns(User $user, Product $product): bool
    {
        return (int) $product->merchant_id === (int) $user->id;
    }
}
