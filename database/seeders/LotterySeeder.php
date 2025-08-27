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
        
        foreach ($products as $product) {
            // Prix de ticket fixe de 1000 FCFA
            $ticketPrice = 1000;
            $maxTickets = intval($product->price / $ticketPrice);
            
            $lotteryNumber = 'LOT-' . strtoupper(uniqid());
            
            // Créer une tombola pour chaque produit avec updateOrInsert
            DB::table('lotteries')->updateOrInsert(
                ['product_id' => $product->id],
                [
                    'lottery_number' => $lotteryNumber,
                    'title' => 'Loterie ' . $product->name,
                    'description' => 'Gagnez un ' . $product->name . ' d\'une valeur de ' . number_format($product->price) . ' FCFA',
                    'product_id' => $product->id,
                    'max_tickets' => $maxTickets,
                    'sold_tickets' => rand(50, min(300, intval($maxTickets * 0.7))),
                    'ticket_price' => $ticketPrice,
                    'draw_date' => now()->addDays(rand(15, 45)),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        
        echo "✅ " . count($products) . " loteries créées.\n";
    }
}