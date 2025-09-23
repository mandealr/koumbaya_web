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
        Schema::table('payouts', function (Blueprint $table) {
            // Ajouter une référence vers la demande de payout marchand
            $table->unsignedBigInteger('merchant_payout_request_id')
                ->nullable()
                ->after('transfert_id')
                ->comment('Demande de payout marchand associée');
            
            // Type de payout
            $table->enum('payout_category', ['transfer', 'merchant_refund', 'system_refund'])
                ->default('transfer')
                ->after('payout_type');
                
            // Qui a initié le payout
            $table->unsignedBigInteger('initiated_by')
                ->nullable()
                ->after('user_id')
                ->comment('Admin ou système qui a initié le payout');
                
            // Index pour les performances
            $table->index('payout_category');
            $table->index('merchant_payout_request_id');
            $table->index('initiated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropIndex(['payout_category']);
            $table->dropIndex(['merchant_payout_request_id']);
            $table->dropIndex(['initiated_by']);
            $table->dropColumn(['merchant_payout_request_id', 'payout_category', 'initiated_by']);
        });
    }
};