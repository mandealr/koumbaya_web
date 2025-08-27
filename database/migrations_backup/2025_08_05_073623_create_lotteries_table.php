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
        Schema::create('lotteries', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('lottery_number')->unique(); // Numéro unique de tombola
            $table->unsignedInteger('product_id');
            $table->integer('total_tickets'); // Nombre total de tickets
            $table->integer('sold_tickets')->default(0); // Tickets vendus
            $table->decimal('ticket_price', 8, 2);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('draw_date')->nullable(); // Date du tirage
            $table->unsignedInteger('winner_user_id')->nullable(); // Gagnant
            $table->string('winner_ticket_number')->nullable(); // Numéro du ticket gagnant
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->boolean('is_drawn')->default(false);
            $table->text('draw_proof')->nullable(); // Preuve de transparence du tirage
            $table->timestamps();
            
            $table->primary('id');
            $table->index('product_id');
            $table->index('winner_user_id');
            $table->index('status');
            $table->index('is_drawn');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotteries');
    }
};
