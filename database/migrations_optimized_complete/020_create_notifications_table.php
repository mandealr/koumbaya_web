<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->enum('type', [
                'ticket_purchase', 
                'lottery_winner', 
                'lottery_draw', 
                'payment_success', 
                'payment_failed', 
                'order_status', 
                'refund_processed', 
                'system_message'
            ]);
            $table->string('title', 255);
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('channel', ['email', 'sms', 'push', 'in_app'])->default('in_app');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamps();
            
            // Index pour performance
            $table->index('user_id');
            $table->index('type');
            $table->index('read_at');
            $table->index('sent_at');
            $table->index('status');
            $table->index('created_at');
            
            // Index composé pour notifications non-lues
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};