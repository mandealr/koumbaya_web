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
        Schema::table('products', function (Blueprint $table) {
            $table->string('sale_mode')->default('direct')->after('merchant_id');
            $table->decimal('ticket_price', 10, 2)->nullable()->change();
            $table->integer('min_participants')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sale_mode');
            $table->decimal('ticket_price', 10, 2)->nullable(false)->change();
            $table->integer('min_participants')->nullable(false)->change();
        });
    }
};
