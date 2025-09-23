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
        Schema::create('merchant_payout_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('merchant_id')->constrained('users')->comment('Marchand qui demande le remboursement');
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('lottery_id')->nullable()->constrained();
            $table->foreignId('product_id')->nullable()->constrained();
            
            // Détails du remboursement
            $table->enum('refund_type', [
                'order_cancellation', 
                'lottery_cancellation', 
                'product_defect', 
                'customer_request', 
                'other'
            ]);
            $table->text('reason')->comment('Raison détaillée du remboursement');
            $table->decimal('refund_amount', 15, 2)->comment('Montant à rembourser en FCFA');
            
            // Détails du client à rembourser
            $table->foreignId('customer_id')->constrained('users')->comment('Client à rembourser');
            $table->string('customer_phone')->comment('Numéro mobile money du client');
            $table->enum('payment_operator', ['airtelmoney', 'moovmoney4']);
            
            // Workflow
            $table->enum('status', [
                'pending', 
                'approved', 
                'rejected', 
                'processing', 
                'completed', 
                'failed'
            ])->default('pending');
            
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Lien avec le payout effectif
            $table->foreignId('payout_id')->nullable()->constrained('payouts');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index(['merchant_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_payout_requests');
    }
};