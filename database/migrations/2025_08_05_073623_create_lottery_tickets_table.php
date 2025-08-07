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
        Schema::create('lottery_tickets', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('ticket_number')->unique(); // Numéro unique du ticket
            $table->unsignedInteger('lottery_id');
            $table->unsignedInteger('user_id'); // Propriétaire du ticket
            $table->decimal('price_paid', 8, 2); // Prix payé pour ce ticket
            $table->string('payment_reference')->nullable(); // Référence de paiement
            $table->enum('status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->boolean('is_winner')->default(false);
            $table->dateTime('purchased_at');
            $table->timestamps();
            
            $table->primary('id');
            $table->index('lottery_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('is_winner');
            $table->index('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lottery_tickets');
    }
};
