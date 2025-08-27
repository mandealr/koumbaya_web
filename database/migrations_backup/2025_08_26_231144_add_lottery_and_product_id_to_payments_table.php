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
            if (!Schema::hasColumn('payments', 'lottery_id')) {
                $table->unsignedBigInteger('lottery_id')->nullable()->after('user_id');
            }
            
            if (!Schema::hasColumn('payments', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('lottery_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'lottery_id')) {
                $table->dropColumn('lottery_id');
            }
            
            if (Schema::hasColumn('payments', 'product_id')) {
                $table->dropColumn('product_id');
            }
        });
    }
};
