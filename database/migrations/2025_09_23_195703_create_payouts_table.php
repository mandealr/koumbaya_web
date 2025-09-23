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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            
            // Références SHAP API
            $table->string('external_reference')->nullable()->comment('Référence SHAP API');
            $table->string('payment_system_name')->comment('Opérateur mobile money');
            $table->string('payee_msisdn')->comment('Numéro du bénéficiaire');
            $table->decimal('amount', 15, 2)->comment('Montant en FCFA');
            $table->string('payout_type')->nullable();
            $table->string('payout_id')->nullable()->comment('ID SHAP');
            $table->string('transaction_id')->nullable()->comment('ID transaction finale');
            $table->text('message')->nullable()->comment('Message de retour SHAP');
            $table->integer('status')->default(0)->comment('Statut SHAP (5=réussi)');
            
            // Relations internes
            $table->unsignedBigInteger('user_id')->nullable()->comment('Utilisateur bénéficiaire');
            $table->unsignedBigInteger('transfert_id')->nullable()->comment('Transfert associé');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('user_id');
            $table->index('transfert_id');
            $table->index('external_reference');
            $table->index('transaction_id');
            $table->index('status');
            $table->index(['payment_system_name', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
