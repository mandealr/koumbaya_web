<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->unique();
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->decimal('pending_balance', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('XAF');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index pour performance
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wallets');
    }
};