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
        Schema::create('draw_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('lottery_id');
            $table->unsignedInteger('winning_ticket_id');
            $table->unsignedInteger('winner_user_id');
            $table->integer('total_participants');
            $table->integer('total_tickets');
            $table->string('draw_method'); // automatic, manual, scheduled
            $table->string('initiated_by'); // system, user_id, admin
            $table->text('draw_seed'); // The seed used for random selection
            $table->string('verification_hash'); // For verifying the draw
            $table->json('participant_snapshot'); // Snapshot of all participants
            $table->json('metadata'); // Additional data
            $table->timestamp('drawn_at');
            $table->timestamps();
            
            $table->index('lottery_id');
            $table->index('winner_user_id');
            $table->index('verification_hash');
            $table->index('drawn_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_histories');
    }
};