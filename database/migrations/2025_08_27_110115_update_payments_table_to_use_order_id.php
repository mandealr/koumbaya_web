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
        Schema::table('payments', function (Blueprint $table) {
            // Add order_id column
            $table->unsignedBigInteger('order_id')->nullable()->after('user_id');
            $table->index('order_id');
            
            // Add callback_data column to store payment callbacks
            $table->json('callback_data')->nullable()->after('gateway_response');
            
            // Remove columns that are now redundant (lottery_id, product_id will come from order)
            $table->dropColumn(['lottery_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Restore lottery_id and product_id columns
            $table->unsignedBigInteger('lottery_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            
            // Remove callback_data and order_id
            $table->dropColumn(['callback_data']);
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
