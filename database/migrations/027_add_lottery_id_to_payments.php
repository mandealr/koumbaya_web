<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->bigInteger('lottery_id')->unsigned()->nullable()->after('order_id');
            $table->bigInteger('product_id')->unsigned()->nullable()->after('lottery_id');
            
            // Index pour performance
            $table->index('lottery_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['lottery_id']);
            $table->dropIndex(['product_id']);
            
            $table->dropColumn(['lottery_id', 'product_id']);
        });
    }
};