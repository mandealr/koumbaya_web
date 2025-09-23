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
            $table->unsignedBigInteger('merchant_id')->comment('Marchand qui demande le remboursement');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('lottery_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            
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
            $table->unsignedBigInteger('customer_id')->comment('Client à rembourser');
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
            
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Lien avec le payout effectif
            $table->unsignedBigInteger('payout_id')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('merchant_id');
            $table->index('order_id');
            $table->index('lottery_id');
            $table->index('product_id');
            $table->index('customer_id');
            $table->index('approved_by');
            $table->index('payout_id');
            $table->index(['merchant_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('request_number');
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