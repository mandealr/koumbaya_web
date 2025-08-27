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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_id')->unique(); // ebilling, airtel_money, flutterwave, etc.
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['gateway', 'mobile_money', 'card', 'bank_transfer']);
            $table->string('icon')->nullable();
            $table->boolean('active')->default(false);
            $table->json('config')->nullable(); // Configuration data (API keys, etc.)
            $table->string('gateway')->nullable(); // Parent gateway for mobile_money types
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
