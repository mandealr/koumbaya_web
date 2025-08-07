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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // lottery_winner, lottery_draw_result, ticket_purchase, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional structured data
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('channel')->default('app'); // app, email, sms, push
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->string('related_type')->nullable(); // Polymorphic relation
            $table->unsignedBigInteger('related_id')->nullable(); // Polymorphic relation
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'read_at']);
            $table->index(['status', 'created_at']);
            $table->index(['related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};