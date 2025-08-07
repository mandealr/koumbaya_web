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
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedInteger('user_id')->unique();
            $table->decimal('balance', 15, 2)->default(0.00); // Solde en FCFA
            $table->decimal('pending_balance', 15, 2)->default(0.00); // Solde en attente
            $table->string('currency', 3)->default('XAF');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->primary('id');
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_wallets');
    }
};
