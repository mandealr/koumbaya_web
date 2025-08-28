<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            // Ajouter les colonnes manquantes du modèle Refund
            $table->bigInteger('transaction_id')->unsigned()->nullable()->after('user_id');
            $table->bigInteger('lottery_id')->unsigned()->nullable()->after('transaction_id');
            $table->enum('type', ['automatic', 'manual', 'system'])->default('manual')->after('reason');
            $table->bigInteger('processed_by')->unsigned()->nullable()->after('processed_at');
            $table->string('external_refund_id', 100)->nullable()->after('refund_transaction_id');
            $table->json('callback_data')->nullable()->after('external_refund_id');
            $table->text('notes')->nullable()->after('callback_data');
            $table->boolean('auto_processed')->default(false)->after('notes');
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->bigInteger('rejected_by')->unsigned()->nullable()->after('rejected_at');
            $table->text('rejection_reason')->nullable()->after('rejected_by');
            
            // Index pour performance
            $table->index('transaction_id');
            $table->index('lottery_id');
            $table->index('type');
            $table->index('processed_by');
            $table->index('auto_processed');
            $table->index('approved_by');
            $table->index('rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['lottery_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['processed_by']);
            $table->dropIndex(['auto_processed']);
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['rejected_by']);
            
            $table->dropColumn([
                'transaction_id',
                'lottery_id', 
                'type',
                'processed_by',
                'external_refund_id',
                'callback_data',
                'notes',
                'auto_processed',
                'rejected_at',
                'rejected_by',
                'rejection_reason'
            ]);
        });
    }
};