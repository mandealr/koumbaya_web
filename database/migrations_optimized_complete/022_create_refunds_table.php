<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number', 50)->unique();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->bigInteger('payment_id')->unsigned()->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('reason', [
                'lottery_cancelled', 
                'product_unavailable', 
                'technical_error', 
                'customer_request', 
                'fraud', 
                'other'
            ]);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'processing', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->string('refund_method', 50)->nullable();
            $table->string('refund_transaction_id', 100)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->bigInteger('approved_by')->unsigned()->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Index pour performance
            $table->index('refund_number');
            $table->index('user_id');
            $table->index('order_id');
            $table->index('payment_id');
            $table->index('status');
            $table->index('reason');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};