<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rated_user_id'); // Utilisateur qui reçoit la note
            $table->unsignedBigInteger('rater_user_id'); // Utilisateur qui donne la note
            $table->unsignedBigInteger('transaction_id')->nullable(); // Transaction liée
            $table->unsignedBigInteger('product_id')->nullable(); // Produit lié
            $table->integer('rating'); // Note de 1 à 5
            $table->text('comment')->nullable(); // Commentaire
            $table->enum('type', ['seller', 'buyer']); // Type d'évaluation
            $table->boolean('is_verified')->default(false); // Évaluation vérifiée
            $table->timestamps();

            // Index
            $table->index('rated_user_id');
            $table->index('rater_user_id');
            $table->index('transaction_id');
            $table->index('product_id');
            $table->index('rating');
            $table->index('type');
            $table->index(['rated_user_id', 'type']);
            
            // Contrainte unique pour éviter les doublons
            $table->unique(['rated_user_id', 'rater_user_id', 'transaction_id'], 'unique_rating_per_transaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ratings');
    }
};