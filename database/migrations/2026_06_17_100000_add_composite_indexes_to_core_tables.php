<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index composés pour les filtres métier fréquents (dashboards, cron de tirage)
 * afin d'éviter les scans sur des tables à forte croissance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'orders_status_created_idx');
            $table->index(['user_id', 'status'], 'orders_user_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'payments_status_created_idx');
            $table->index(['user_id', 'status'], 'payments_user_status_idx');
        });

        Schema::table('lotteries', function (Blueprint $table) {
            $table->index(['status', 'draw_date'], 'lotteries_status_draw_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_created_idx');
            $table->dropIndex('orders_user_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_created_idx');
            $table->dropIndex('payments_user_status_idx');
        });

        Schema::table('lotteries', function (Blueprint $table) {
            $table->dropIndex('lotteries_status_draw_idx');
        });
    }
};
