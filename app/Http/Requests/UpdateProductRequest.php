<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request de mise à jour d'un produit.
 *
 * Centralise l'autorisation (ProductPolicy::update) et la validation, qui
 * étaient jusqu'ici éparpillées en inline dans le contrôleur.
 */
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = Product::find($this->route('id'));

        return $product !== null && (bool) $this->user()?->can('update', $product);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $minProductPrice = config('koumbaya.marketplace.min_product_price', 1000);
        $minTicketPrice = config('koumbaya.ticket_calculation.min_ticket_price', 200);

        return [
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => "numeric|min:{$minProductPrice}",
            'ticket_price' => "numeric|min:{$minTicketPrice}",
            'images' => 'nullable|array',
            'status' => 'in:draft,active',
            'vendor_profile_id' => 'nullable|exists:vendor_profiles,id',
        ];
    }
}
