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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'transaction_id')) {
                $table->string('transaction_id')->unique()->nullable()->after('reference');
            }
            if (!Schema::hasColumn('transactions', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('lottery_ticket_id');
            }
            if (!Schema::hasColumn('transactions', 'quantity')) {
                $table->integer('quantity')->default(1)->after('phone_number');
            }
            if (!Schema::hasColumn('transactions', 'payment_provider')) {
                $table->string('payment_provider')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('transactions', 'payment_provider_id')) {
                $table->string('payment_provider_id')->nullable()->after('payment_provider');
            }
            if (!Schema::hasColumn('transactions', 'callback_data')) {
                $table->json('callback_data')->nullable()->after('payment_provider_id');
            }
            if (!Schema::hasColumn('transactions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('transactions', 'failed_at')) {
                $table->timestamp('failed_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('transactions', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('failed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_id',
                'phone_number',
                'quantity',
                'payment_provider',
                'payment_provider_id',
                'callback_data',
                'completed_at',
                'failed_at',
                'failure_reason'
            ]);
        });
    }
};