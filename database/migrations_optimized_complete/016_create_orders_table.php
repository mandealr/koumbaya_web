<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->bigInteger('user_id')->unsigned();
            $table->enum('type', ['lottery', 'direct']);
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->bigInteger('lottery_id')->unsigned()->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('status', ['pending', 'awaiting_payment', 'paid', 'failed', 'cancelled', 'fulfilled', 'refunded'])->default('pending');
            $table->string('payment_reference', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Index pour performance - CŒUR DU SYSTÈME
            $table->index('order_number');
            $table->index('user_id');
            $table->index('product_id');
            $table->index('lottery_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};