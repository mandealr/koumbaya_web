<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_id', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('type', ['gateway', 'mobile_money', 'card', 'bank_transfer']);
            $table->string('icon', 255)->nullable();
            $table->boolean('active')->default(false);
            $table->json('config')->nullable();
            $table->string('gateway', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Index pour performance
            $table->index(['active', 'sort_order']);
            $table->index('type');
            $table->index('gateway');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};