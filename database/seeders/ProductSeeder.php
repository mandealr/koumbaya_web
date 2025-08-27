<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l'ID d'un marchand pour créer des produits
        $sellerId = User::where('email', 'merchant1@koumbaya.ga')->first()->id;
        
        // Récupérer les IDs des catégories
        $categories = Category::pluck('id', 'slug');
        
        // Fonction helper pour récupérer une catégorie avec fallback
        $getCategory = function($slug) use ($categories) {
            // Mapping des anciens vers nouveaux slugs
            $mapping = [
                'electronique' => 'smartphones-tablettes',
                'informatique' => 'ordinateurs-informatique', 
                'automobile' => 'voitures',
                'maison' => 'electromenager'
            ];
            
            // Si le slug existe directement
            if (isset($categories[$slug])) {
                return $categories[$slug];
            }
            
            // Si c'est un ancien slug, utiliser le mapping
            if (isset($mapping[$slug]) && isset($categories[$mapping[$slug]])) {
                return $categories[$mapping[$slug]];
            }
            
            // Fallback vers la première catégorie disponible
            return $categories->first();
        };
        
        $products = [
            [
                'name' => 'iPhone 15 Pro Max 256GB',
                'description' => 'Le dernier iPhone d\'Apple avec puce A17 Pro, écran Super Retina XDR de 6,7 pouces.',
                'image' => '/images/products/placeholder.jpg',
                'price' => 1299000,
                'currency' => 'XAF',
                'stock_quantity' => 1,
                'sale_mode' => 'lottery',
                'category_id' => $getCategory('smartphones-tablettes'),
                'merchant_id' => $sellerId,
                'is_featured' => true,
                'is_active' => true,
                'views_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MacBook Pro M3 14"',
                'description' => 'MacBook Pro 14 pouces avec puce M3, 16GB RAM, 512GB SSD.',
                'image' => '/images/products/placeholder.jpg',
                'price' => 2500000,
                'currency' => 'XAF',
                'stock_quantity' => 1,
                'sale_mode' => 'lottery',
                'category_id' => $getCategory('ordinateurs-informatique'),
                'merchant_id' => $sellerId,
                'is_featured' => true,
                'is_active' => true,
                'views_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PlayStation 5',
                'description' => 'Console PlayStation 5 avec manette DualSense.',
                'image' => '/images/products/placeholder.jpg',
                'price' => 750000,
                'currency' => 'XAF',
                'stock_quantity' => 1,
                'sale_mode' => 'lottery',
                'category_id' => $getCategory('gaming'),
                'merchant_id' => $sellerId,
                'is_featured' => true,
                'is_active' => true,
                'views_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Créer les produits avec firstOrCreate pour éviter les doublons
        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['name' => $productData['name'], 'merchant_id' => $productData['merchant_id']],
                $productData
            );
        }

        echo "✅ " . count($products) . " produits de démonstration créés.\n";
    }
}