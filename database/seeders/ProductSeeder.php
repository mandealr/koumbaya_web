<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                'slug' => 'iphone-15-pro-max-256gb',
                'description' => 'Le dernier iPhone d\'Apple avec puce A17 Pro, écran Super Retina XDR de 6,7 pouces, système photo pro avancé avec zoom optique 5x. Neuf, garantie officielle Apple.',
                'images' => json_encode(['/images/products/iphone15pro.jpg', '/images/products/iphone15pro-2.jpg']),
                'price' => 1299000, // 1,299,000 FCFA
                'ticket_price' => 1500, // 1,500 FCFA par ticket
                'min_participants' => 500,
                'stock_quantity' => 1,
                'category_id' => $categories['electronique'],
                'merchant_id' => $sellerId,
                'status' => 'active',
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MacBook Pro M3 14"',
                'slug' => 'macbook-pro-m3-14',
                'description' => 'MacBook Pro 14 pouces avec puce M3, 16GB RAM, 512GB SSD. Parfait pour les professionnels et créatifs. Clavier Magic Keyboard rétroéclairé.',
                'images' => json_encode(['/images/products/macbook-m3.jpg']),
                'price' => 2500000, // 2,500,000 FCFA
                'ticket_price' => 2500, // 2,500 FCFA par ticket
                'min_participants' => 800,
                'stock_quantity' => 1,
                'category_id' => $categories['informatique'],
                'merchant_id' => $sellerId,
                'status' => 'active',
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PlayStation 5 + 2 Manettes',
                'slug' => 'playstation-5-2-manettes',
                'description' => 'Console PlayStation 5 avec 2 manettes DualSense, support vertical et câbles inclus. Expérience gaming next-gen avec SSD ultra-rapide.',
                'images' => json_encode(['/images/products/ps5.jpg']),
                'price' => 750000, // 750,000 FCFA
                'ticket_price' => 1000, // 1,000 FCFA par ticket
                'min_participants' => 400,
                'stock_quantity' => 1,
                'category_id' => $categories['gaming'],
                'merchant_id' => $sellerId,
                'status' => 'active',
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'slug' => 'samsung-galaxy-s24-ultra',
                'description' => 'Smartphone Samsung Galaxy S24 Ultra avec S Pen intégré, écran Dynamic AMOLED 6,8", 256GB, appareil photo 200MP avec zoom 100x.',
                'images' => json_encode(['/images/products/galaxy-s24.jpg']),
                'price' => 950000, // 950,000 FCFA
                'ticket_price' => 1200, // 1,200 FCFA par ticket
                'min_participants' => 600,
                'stock_quantity' => 1,
                'category_id' => $categories['electronique'],
                'merchant_id' => $sellerId,
                'status' => 'active',
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tesla Model 3 Standard',
                'slug' => 'tesla-model-3-standard',
                'description' => 'Véhicule électrique Tesla Model 3, autonomie 500km, Autopilot inclus, écran tactile 15", 0-100km/h en 5,8s. Véhicule neuf avec garantie.',
                'images' => json_encode(['/images/products/tesla-model3.jpg']),
                'price' => 45000000, // 45,000,000 FCFA
                'ticket_price' => 50000, // 50,000 FCFA par ticket
                'min_participants' => 900,
                'stock_quantity' => 1,
                'category_id' => $categories['automobile'],
                'merchant_id' => $sellerId,
                'status' => 'active',
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Réfrigérateur Samsung 500L',
                'slug' => 'refrigerateur-samsung-500l',
                'description' => 'Réfrigérateur Samsung 500L No Frost, distributeur d\'eau, éclairage LED, classe énergétique A++. Garantie 2 ans.',
                'images' => json_encode(['/images/products/frigo-samsung.jpg']),
                'price' => 850000, // 850,000 FCFA
                'ticket_price' => 1100, // 1,100 FCFA par ticket
                'min_participants' => 500,
                'stock_quantity' => 1,
                'category_id' => $categories['maison'],
                'merchant_id' => $sellerId,
                'status' => 'active',
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Apple Watch Series 9',
                'slug' => 'apple-watch-series-9',
                'description' => 'Apple Watch Series 9 45mm, GPS + Cellular, boîtier aluminium, bracelet sport. Suivi santé avancé, GPS précis, résistante à l\'eau.',
                'images' => json_encode(['/images/products/apple-watch.jpg']),
                'price' => 450000, // 450,000 FCFA
                'ticket_price' => 800, // 800 FCFA par ticket
                'min_participants' => 350,
                'stock_quantity' => 1,
                'category_id' => $categories['electronique'],
                'merchant_id' => $sellerId,
                'status' => 'active',
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'slug' => 'nintendo-switch-oled',
                'description' => 'Console Nintendo Switch modèle OLED avec écran 7 pouces, dock, Joy-Con et tous les accessoires. Parfaite pour jouer partout.',
                'images' => json_encode(['/images/products/switch-oled.jpg']),
                'price' => 380000, // 380,000 FCFA
                'ticket_price' => 600, // 600 FCFA par ticket
                'min_participants' => 400,
                'stock_quantity' => 1,
                'category_id' => $categories['gaming'],
                'merchant_id' => $sellerId,
                'status' => 'active',
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('products')->insert($products);
    }
}