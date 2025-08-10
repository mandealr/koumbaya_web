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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('lottery_id')->nullable();
            
            // Refund details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('XAF');
            $table->string('reason'); // insufficient_participants, lottery_cancelled, system_error, etc.
            $table->enum('type', ['automatic', 'manual', 'admin'])->default('automatic');
            
            // Status and processing
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed', 'completed', 'failed'])
                  ->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            
            // Refund method and external tracking
            $table->string('refund_method')->default('mobile_money'); // mobile_money, bank_transfer, wallet_credit
            $table->string('external_refund_id')->nullable();
            $table->json('callback_data')->nullable();
            
            // Admin approval workflow
            $table->boolean('auto_processed')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Additional notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('lottery_id');
            $table->index('processed_by');
            $table->index('approved_by');
            $table->index('rejected_by');
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['lottery_id', 'reason']);
            $table->index(['type', 'auto_processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};