<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Références essentielles
            $table->string('reference', 100)->unique();
            $table->bigInteger('order_id')->unsigned();
            
            // E-Billing IDs
            $table->string('ebilling_id', 100)->nullable();
            $table->string('external_transaction_id', 100)->nullable();
            
            // Informations de paiement
            $table->string('payment_method', 50)->nullable();
            $table->decimal('amount', 15, 2);
            
            // Statut E-Billing
            $table->enum('status', ['created', 'pending', 'paid', 'processed', 'expired', 'failed', 'refunded'])->default('created');
            
            // Données de callback
            $table->json('callback_data')->nullable();
            
            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Champs essentiels conservés de transactions
            $table->bigInteger('user_id')->unsigned();
            $table->string('transaction_id', 100)->nullable(); // legacy support
            $table->string('currency', 3)->default('XAF');
            
            // Champ meta pour anciens champs (customer_*, gateway_*, description, etc.)
            $table->json('meta')->nullable();
            
            // Index E-Billing optimisés
            $table->index('reference');
            $table->index('order_id');
            $table->index('user_id');
            $table->index('ebilling_id');
            $table->index('external_transaction_id');
            $table->index('transaction_id'); // legacy support
            $table->index('payment_method');
            $table->index('status');
            $table->index('created_at');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};