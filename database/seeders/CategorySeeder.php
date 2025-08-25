<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Créer des catégories de produits complètes pour le marketplace Koumbaya.
     * Inclut 25 catégories couvrant tous les types de produits susceptibles
     * d'être vendus dans des tombolas : tech, maison, mode, automobile, luxe, etc.
     */
    public function run(): void
    {
        // Vider la table d'abord (pour éviter les doublons lors du re-seeding)
        DB::table('categories')->truncate();

        $categories = [
            // === ÉLECTRONIQUE ===
            [
                'name' => 'Smartphones & Tablettes',
                'slug' => 'smartphones-tablettes',
                'description' => 'iPhone, Samsung Galaxy, iPad, tablettes Android',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ordinateurs & Informatique',
                'slug' => 'ordinateurs-informatique',
                'description' => 'MacBook, PC portables, ordinateurs de bureau, imprimantes',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Audio & Vidéo',
                'slug' => 'audio-video',
                'description' => 'Écouteurs, enceintes Bluetooth, TV, projecteurs',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gaming',
                'slug' => 'gaming',
                'description' => 'PlayStation, Xbox, Nintendo Switch, PC Gaming',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accessoires Tech',
                'slug' => 'accessoires-tech',
                'description' => 'Coques, chargeurs, câbles, supports, power banks',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === MAISON & JARDIN ===
            [
                'name' => 'Électroménager',
                'slug' => 'electromenager',
                'description' => 'Réfrigérateurs, lave-linge, micro-ondes, aspirateurs',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mobilier',
                'slug' => 'mobilier',
                'description' => 'Canapés, tables, chaises, armoires, lits',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Décoration',
                'slug' => 'decoration',
                'description' => 'Tableaux, vases, coussins, luminaires, plantes',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jardin & Extérieur',
                'slug' => 'jardin-exterieur',
                'description' => 'Mobilier de jardin, plantes, outils de jardinage, barbecue',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === MODE & ACCESSOIRES ===
            [
                'name' => 'Mode Homme',
                'slug' => 'mode-homme',
                'description' => 'Vêtements, chaussures, accessoires pour hommes',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mode Femme',
                'slug' => 'mode-femme',
                'description' => 'Vêtements, chaussures, sacs, bijoux pour femmes',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Montres & Bijoux',
                'slug' => 'montres-bijoux',
                'description' => 'Montres de luxe, bijoux, alliances, colliers',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maroquinerie',
                'slug' => 'maroquinerie',
                'description' => 'Sacs à main, portefeuilles, ceintures, bagages',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === AUTOMOBILE ===
            [
                'name' => 'Voitures',
                'slug' => 'voitures',
                'description' => 'Voitures neuves et d\'occasion, véhicules électriques',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Motos & Scooters',
                'slug' => 'motos-scooters',
                'description' => 'Motos, scooters, équipements motards',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accessoires Auto',
                'slug' => 'accessoires-auto',
                'description' => 'Pièces détachées, pneus, GPS, dashcam',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === SPORT & LOISIRS ===
            [
                'name' => 'Sport & Fitness',
                'slug' => 'sport-fitness',
                'description' => 'Équipements sportifs, fitness, vélos, running',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Voyage & Bagages',
                'slug' => 'voyage-bagages',
                'description' => 'Valises, sacs de voyage, accessoires de voyage',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Instruments de Musique',
                'slug' => 'instruments-musique',
                'description' => 'Guitares, pianos, DJ, home studio',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === BEAUTÉ & SANTÉ ===
            [
                'name' => 'Beauté & Cosmétiques',
                'slug' => 'beaute-cosmetiques',
                'description' => 'Parfums, maquillage, soins visage et corps',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Santé & Bien-être',
                'slug' => 'sante-bien-etre',
                'description' => 'Compléments alimentaires, matériel médical, spa',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === PREMIUM & LUXE ===
            [
                'name' => 'Articles de Luxe',
                'slug' => 'articles-luxe',
                'description' => 'Produits haut de gamme, éditions limitées, collection',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Art & Collection',
                'slug' => 'art-collection',
                'description' => 'Œuvres d\'art, objets de collection, antiquités',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === BONS & SERVICES ===
            [
                'name' => 'Bons d\'Achat',
                'slug' => 'bons-achat',
                'description' => 'Cartes cadeaux, bons d\'achat magasins, e-commerce',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Expériences & Loisirs',
                'slug' => 'experiences-loisirs',
                'description' => 'Week-ends, restaurants, activités, spectacles',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('categories')->insert($categories);
    }
}