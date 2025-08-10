<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les utilisateurs
        $users = DB::table('users')->get();
        
        if ($users->count() < 2) {
            return; // Pas assez d'utilisateurs pour créer des évaluations
        }
        
        $ratings = [];
        $ratingId = 1;
        
        foreach ($users as $ratedUser) {
            // Chaque utilisateur reçoit entre 1 et 5 évaluations
            $numRatings = rand(1, 5);
            $totalRating = 0;
            
            for ($i = 0; $i < $numRatings; $i++) {
                // Choisir un évaluateur aléatoire différent
                $availableRaters = $users->filter(function ($user) use ($ratedUser) {
                    return $user->id !== $ratedUser->id;
                });
                
                if ($availableRaters->count() === 0) {
                    continue;
                }
                
                $rater = $availableRaters->random();
                $rating = rand(3, 5); // Notes entre 3 et 5 pour avoir de bonnes moyennes
                $totalRating += $rating;
                
                $comments = [
                    'Excellent vendeur, produit conforme à la description.',
                    'Transaction rapide et sécurisée, très satisfait !',
                    'Personne de confiance, je recommande vivement.',
                    'Communication parfaite, livraison dans les temps.',
                    'Très bon acheteur, paiement rapide.',
                    'Service client au top, rien à redire.',
                    'Produit de qualité, exactement ce que j\'attendais.',
                    'Vendeur sérieux et professionnel.',
                ];
                
                $types = ['seller', 'buyer'];
                
                $ratings[] = [
                    'rated_user_id' => $ratedUser->id,
                    'rater_user_id' => $rater->id,
                    'transaction_id' => null, // Pas de transaction spécifique pour l'instant
                    'product_id' => null,
                    'rating' => $rating,
                    'comment' => $comments[array_rand($comments)],
                    'type' => $types[array_rand($types)],
                    'is_verified' => rand(0, 1) == 1,
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ];
            }
            
            // Calculer la moyenne et mettre à jour l'utilisateur
            if ($numRatings > 0) {
                $averageRating = round($totalRating / $numRatings, 2);
                
                DB::table('users')
                    ->where('id', $ratedUser->id)
                    ->update([
                        'rating' => $averageRating,
                        'rating_count' => $numRatings,
                        'updated_at' => now(),
                    ]);
            }
        }
        
        // Insérer tous les ratings
        if (!empty($ratings)) {
            // Diviser en chunks pour éviter les erreurs de mémoire
            $chunks = array_chunk($ratings, 50);
            foreach ($chunks as $chunk) {
                DB::table('user_ratings')->insert($chunk);
            }
        }
    }
}