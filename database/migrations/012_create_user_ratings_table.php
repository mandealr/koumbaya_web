<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_ratings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('rated_user_id')->unsigned();
            $table->bigInteger('rater_user_id')->unsigned();
            $table->bigInteger('payment_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->enum('type', ['seller', 'buyer']);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            // Index pour performance
            $table->index('rated_user_id');
            $table->index('rater_user_id');
            $table->index('payment_id');
            $table->index('product_id');
            $table->index('rating');
            $table->index('type');
            $table->index(['rated_user_id', 'type']);
            
            // Contrainte unique pour éviter les doublons
            $table->unique(['rated_user_id', 'rater_user_id', 'payment_id'], 'unique_rating_per_payment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ratings');
    }
};