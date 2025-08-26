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
            if (!Schema::hasColumn('payments', 'billing_id')) {
                $table->string('billing_id')->nullable()->after('reference');
            }
            if (!Schema::hasColumn('payments', 'timeout')) {
                $table->integer('timeout')->nullable()->after('billing_id');
            }
            if (!Schema::hasColumn('payments', 'operator')) {
                $table->string('operator')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('operator');
            }
            if (!Schema::hasColumn('payments', 'error_message')) {
                $table->text('error_message')->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('payments', 'billing_id')) $columns[] = 'billing_id';
            if (Schema::hasColumn('payments', 'timeout')) $columns[] = 'timeout';
            if (Schema::hasColumn('payments', 'operator')) $columns[] = 'operator';
            if (Schema::hasColumn('payments', 'paid_at')) $columns[] = 'paid_at';
            if (Schema::hasColumn('payments', 'error_message')) $columns[] = 'error_message';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
