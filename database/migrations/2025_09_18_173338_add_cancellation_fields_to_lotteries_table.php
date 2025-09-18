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
        Schema::table('lotteries', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable();
            $table->string('cancellation_reason_type')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->index(['status', 'cancelled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lotteries', function (Blueprint $table) {
            $table->dropIndex(['status', 'cancelled_at']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancellation_reason', 'cancellation_reason_type', 'cancelled_at', 'cancelled_by']);
        });
    }
};
