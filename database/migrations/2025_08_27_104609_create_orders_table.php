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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['lottery', 'direct']);
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('lottery_id')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('status', [
                'pending', 
                'awaiting_payment', 
                'paid', 
                'failed', 
                'cancelled', 
                'fulfilled', 
                'refunded'
            ])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('order_number');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
