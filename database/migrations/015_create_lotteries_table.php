<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotteries', function (Blueprint $table) {
            $table->id();
            $table->string('lottery_number', 50)->unique();
            $table->string('title', 255);
            $table->text('description');
            $table->bigInteger('product_id')->unsigned();
            $table->decimal('ticket_price', 15, 2);
            $table->string('currency', 3)->default('XAF');
            $table->integer('max_tickets');
            $table->integer('sold_tickets')->default(0);
            $table->timestamp('draw_date')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->string('winning_ticket_number', 50)->nullable();
            $table->bigInteger('winner_user_id')->unsigned()->nullable();
            $table->text('draw_process_info')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Index pour performance
            $table->index('lottery_number');
            $table->index('product_id');
            $table->index('status');
            $table->index('draw_date');
            $table->index('winner_user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotteries');
    }
};