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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('name')->unique(); // ebilling, stripe, paypal
            $table->string('display_name'); // E-Billing, Stripe, PayPal
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            
            // Configuration spécifique pour chaque gateway
            $table->json('config'); // username, password, shared_key pour ebilling, etc.
            
            // URLs spécifiques
            $table->string('api_url')->nullable(); // URL de l'API
            $table->string('redirect_url')->nullable(); // URL de redirection
            
            // Méthodes de paiement supportées
            $table->json('supported_methods')->nullable(); // ['airtel_money', 'mobicash', 'visa']
            
            // Devises supportées
            $table->json('supported_currencies')->nullable();
            
            $table->timestamps();
            
            $table->primary('id');
            $table->index('is_active');
            $table->index('environment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
