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
        Schema::create('transactions', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('reference')->unique(); // Référence unique pour E-Billing
            $table->unsignedInteger('user_id');
            $table->enum('type', ['ticket_purchase', 'direct_purchase', 'refund', 'commission']);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('status', ['created', 'pending', 'paid', 'failed', 'expired', 'refunded'])->default('created');
            $table->string('payment_method')->nullable(); // airtel_money, mobicash, etc.
            $table->string('external_transaction_id')->nullable(); // ID de transaction externe
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('lottery_id')->nullable();
            $table->unsignedInteger('lottery_ticket_id')->nullable();
            $table->text('description');
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            
            $table->primary('id');
            $table->index('user_id');
            $table->index('status');
            $table->index('type');
            $table->index('product_id');
            $table->index('lottery_id');
            $table->index('external_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
