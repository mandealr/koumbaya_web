<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draw_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('lottery_id')->unsigned();
            $table->enum('method', ['auto', 'manual'])->default('auto');
            $table->string('seed', 100)->nullable();
            $table->integer('total_tickets');
            $table->integer('winning_position');
            $table->string('winning_ticket_number', 50);
            $table->bigInteger('winner_user_id')->unsigned();
            $table->bigInteger('drawn_by')->unsigned()->nullable();
            $table->json('draw_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('drawn_at');
            $table->timestamps();
            
            // Index pour performance
            $table->index('lottery_id');
            $table->index('winning_ticket_number');
            $table->index('winner_user_id');
            $table->index('drawn_by');
            $table->index('drawn_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_histories');
    }
};