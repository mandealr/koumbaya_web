<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LotterySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les produits
        $products = DB::table('products')->get();
        
        $globalTicketNumber = 1; // Compteur global pour éviter les doublons
        
        foreach ($products as $product) {
            // Calculer le nombre total de tickets basé sur le prix et le prix du ticket
            $totalTickets = intval($product->price / $product->ticket_price);
            
            // Créer une tombola pour chaque produit
            $lotteryId = DB::table('lotteries')->insertGetId([
                'lottery_number' => 'LOT-' . strtoupper(uniqid()),
                'product_id' => $product->id,
                'total_tickets' => $totalTickets,
                'sold_tickets' => rand(50, min(300, intval($totalTickets * 0.7))), // Entre 50 et 70% des tickets vendus
                'ticket_price' => $product->ticket_price,
                'start_date' => now()->subDays(rand(1, 10)),
                'end_date' => now()->addDays(rand(15, 45)),
                'status' => 'active',
                'draw_date' => null,
                'winner_user_id' => null,
                'winner_ticket_number' => null,
                'is_drawn' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Créer quelques tickets vendus pour chaque tombola
            $lottery = DB::table('lotteries')->find($lotteryId);
            $soldTickets = $lottery->sold_tickets;
            
            // Récupérer les buyers (utilisateurs avec buyer dans l'email pour simplifier)
            $buyers = DB::table('users')
                ->where('email', 'like', '%buyer%')
                ->select('id')
                ->get();
            
            if ($buyers->count() > 0) {
                // Créer des tickets pour les buyers
                $ticketsPerBuyer = intval($soldTickets / $buyers->count());
                $remainingTickets = $soldTickets % $buyers->count();
                
                foreach ($buyers as $buyer) {
                    $ticketsForThisBuyer = $ticketsPerBuyer + ($remainingTickets > 0 ? 1 : 0);
                    $remainingTickets--;
                    
                    for ($i = 0; $i < $ticketsForThisBuyer; $i++) {
                        DB::table('lottery_tickets')->insert([
                            'lottery_id' => $lotteryId,
                            'user_id' => $buyer->id,
                            'ticket_number' => sprintf('%06d', $globalTicketNumber),
                            'price_paid' => $product->ticket_price,
                            'payment_reference' => 'PAY-' . strtoupper(uniqid()),
                            'status' => 'paid',
                            'is_winner' => false,
                            'purchased_at' => now()->subDays(rand(1, 8)),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        $globalTicketNumber++;
                    }
                    
                    // Créer une transaction pour l'achat des tickets
                    if ($ticketsForThisBuyer > 0) {
                        DB::table('transactions')->insert([
                            'user_id' => $buyer->id,
                            'type' => 'ticket_purchase',
                            'amount' => $ticketsForThisBuyer * $product->ticket_price,
                            'currency' => 'XAF',
                            'status' => 'paid',
                            'description' => "Achat de {$ticketsForThisBuyer} tickets pour {$product->name}",
                            'reference' => 'TKT-' . strtoupper(uniqid()),
                            'created_at' => now()->subDays(rand(1, 8)),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
        
        // Marquer quelques tombolas comme complètes avec des gagnants
        $completeLotteries = DB::table('lotteries')
            ->orderBy('id')
            ->limit(2)
            ->get();
            
        foreach ($completeLotteries as $lottery) {
            // Récupérer un ticket aléatoire pour cette tombola
            $winningTicket = DB::table('lottery_tickets')
                ->where('lottery_id', $lottery->id)
                ->inRandomOrder()
                ->first();
            
            if ($winningTicket) {
                DB::table('lotteries')
                    ->where('id', $lottery->id)
                    ->update([
                        'status' => 'completed',
                        'draw_date' => now()->subDays(rand(1, 5)),
                        'winner_user_id' => $winningTicket->user_id,
                        'winner_ticket_number' => $winningTicket->ticket_number,
                        'is_drawn' => true,
                        'updated_at' => now(),
                    ]);
                    
                // Marquer le ticket comme gagnant
                DB::table('lottery_tickets')
                    ->where('id', $winningTicket->id)
                    ->update([
                        'is_winner' => true,
                        'updated_at' => now(),
                    ]);
            }
        }
    }
}