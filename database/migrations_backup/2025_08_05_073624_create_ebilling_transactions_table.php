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
        Schema::create('payments', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('reference')->unique(); // Notre référence unique interne
            $table->unsignedInteger('transaction_id'); // Lien vers table transactions
            $table->unsignedInteger('user_id');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('XAF');
            
            // Informations client pour les gateways
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email');
            $table->text('description');
            
            // Gateway de paiement (ebilling, stripe, paypal, etc.)
            $table->string('payment_gateway')->default('ebilling'); 
            $table->json('gateway_config')->nullable(); // Config spécifique au gateway
            
            // Données spécifiques E-Billing
            $table->string('ebilling_id')->nullable(); // bill_id retourné par E-Billing
            $table->string('success_url')->nullable();
            $table->string('callback_url')->nullable();
            
            // Données de callback/notification
            $table->string('payment_method')->nullable(); // airtel_money, mobicash, visa, etc.
            $table->string('external_transaction_id')->nullable(); // ID de l'opérateur/bank
            $table->json('gateway_response')->nullable(); // Réponse complète du gateway
            
            // Status selon cycle E-Billing
            $table->enum('status', ['created', 'pending', 'paid', 'processed', 'expired', 'failed', 'refunded'])->default('created');
            
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('processed_at')->nullable(); // Quand le callback a été traité
            $table->timestamps();
            
            $table->primary('id');
            $table->index('transaction_id');
            $table->index('user_id');
            $table->index('payment_gateway');
            $table->index('ebilling_id');
            $table->index('status');
            $table->index('external_transaction_id');
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
