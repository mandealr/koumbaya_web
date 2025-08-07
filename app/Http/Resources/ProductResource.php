<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title, // Alias pour compatibilité mobile
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'ticket_price' => (float) $this->ticket_price,
            'min_participants' => $this->min_participants,
            'stock_quantity' => $this->stock_quantity,
            'images' => $this->images && is_array($this->images) ? $this->images : [],
            'image_url' => $this->image_url,
            'main_image' => $this->main_image,
            'category_id' => $this->category_id,
            'merchant_id' => $this->merchant_id,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_available' => $this->is_available,
            'has_active_lottery' => $this->has_active_lottery,
            'lottery_ends_soon' => $this->lottery_ends_soon,
            'popularity_score' => $this->popularity_score,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relations conditionnelles
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            
            'merchant' => $this->whenLoaded('merchant', function () {
                return [
                    'id' => $this->merchant->id,
                    'name' => $this->merchant->full_name,
                    'email' => $this->merchant->email,
                ];
            }),
            
            'active_lottery' => $this->whenLoaded('activeLottery', function () {
                return $this->activeLottery ? new LotteryResource($this->activeLottery) : null;
            }),
            
            'latest_lottery' => $this->whenLoaded('latestLottery', function () {
                return $this->latestLottery ? new LotteryResource($this->latestLottery) : null;
            }),
            
            'lotteries' => LotteryResource::collection($this->whenLoaded('lotteries')),
            
            // Statistiques
            'stats' => $this->when($request->get('include_stats'), function () {
                return $this->stats;
            }),
        ];
    }
}