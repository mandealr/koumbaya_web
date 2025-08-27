<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l'ID d'un marchand pour créer des produits
        $sellerId = DB::table('users')->where('email', 'merchant1@koumbaya.ga')->first()->id;
        
        // Récupérer les IDs des catégories
        $categories = DB::table('categories')->pluck('id', 'slug');
        
        $products = [
            [
                'name' => 'iPhone 15 Pro Max 256GB',
                'description' => 'Le dernier iPhone d\'Apple avec puce A17 Pro, écran Super Retina XDR de 6,7 pouces.',
                'image' => '/images/products/placeholder.jpg',
                'price' => 1299000,
                'stock_quantity' => 1,
                'sale_mode' => 'lottery',
                'category_id' => $categories['smartphones-tablettes'],
                'merchant_id' => $sellerId,
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MacBook Pro M3 14"',
                'description' => 'MacBook Pro 14 pouces avec puce M3, 16GB RAM, 512GB SSD.',
                'image' => '/images/products/placeholder.jpg',
                'price' => 2500000,
                'stock_quantity' => 1,
                'sale_mode' => 'lottery',
                'category_id' => $categories['ordinateurs-informatique'],
                'merchant_id' => $sellerId,
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PlayStation 5',
                'description' => 'Console PlayStation 5 avec manette DualSense.',
                'image' => '/images/products/placeholder.jpg',
                'price' => 750000,
                'stock_quantity' => 1,
                'sale_mode' => 'lottery',
                'category_id' => $categories['gaming'],
                'merchant_id' => $sellerId,
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insérer les produits
        DB::table('products')->insert($products);

        echo "✅ " . count($products) . " produits de démonstration créés.\n";
    }
}