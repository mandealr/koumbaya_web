<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique();
            $table->bigInteger('lottery_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->bigInteger('payment_id')->unsigned()->nullable();
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('status', ['reserved', 'paid', 'cancelled', 'refunded'])->default('reserved');
            $table->boolean('is_winner')->default(false);
            $table->timestamp('purchased_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Index pour performance
            $table->index('ticket_number');
            $table->index('lottery_id');
            $table->index('user_id');
            $table->index('order_id');
            $table->index('payment_id');
            $table->index('status');
            $table->index('is_winner');
            
            // Index composés pour requêtes complexes
            $table->index(['lottery_id', 'user_id']);
            $table->index(['lottery_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_tickets');
    }
};