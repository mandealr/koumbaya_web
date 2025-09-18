<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotteryResource extends JsonResource
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
            'lottery_number' => $this->lottery_number,
            'product_id' => $this->product_id,
            'total_tickets' => $this->total_tickets,
            'sold_tickets' => $this->sold_tickets,
            'remaining_tickets' => $this->remaining_tickets,
            'ticket_price' => (float) $this->ticket_price,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'draw_date' => $this->draw_date,
            'status' => $this->status,
            'is_drawn' => $this->is_drawn,
            'is_expired' => $this->is_expired,
            'can_draw' => $this->can_draw,
            'progress_percentage' => $this->progress_percentage,
            'participation_rate' => $this->participation_rate,
            'time_remaining' => $this->time_remaining,
            'is_ending_soon' => $this->is_ending_soon,
            'winner_user_id' => $this->winner_user_id,
            'winner_ticket_number' => $this->winning_ticket_number,
            'total_revenue' => $this->getTotalRevenue(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relations conditionnelles
            'product' => $this->whenLoaded('product', function () {
                return new ProductResource($this->product);
            }),
            
            'winner' => $this->whenLoaded('winner', function () {
                return $this->winner ? [
                    'id' => $this->winner->id,
                    'name' => $this->winner->full_name,
                    'email' => $this->winner->email,
                ] : null;
            }),
            
            'tickets' => $this->whenLoaded('tickets', function () {
                return $this->tickets->map(function ($ticket) {
                    return [
                        'id' => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'status' => $ticket->status,
                        'is_winner' => $ticket->is_winner,
                        'user_id' => $ticket->user_id,
                        'created_at' => $ticket->created_at,
                    ];
                });
            }),
            
            // Informations sur l'utilisateur connecté
            'user_tickets' => $this->when(auth()->check(), function () {
                return $this->getUserTicketCount(auth()->user());
            }),
            
            'can_participate' => $this->when(auth()->check(), function () {
                return $this->canUserParticipate(auth()->user());
            }),
        ];
    }
}