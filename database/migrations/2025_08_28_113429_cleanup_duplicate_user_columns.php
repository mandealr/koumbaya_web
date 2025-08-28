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
        Schema::table('users', function (Blueprint $table) {
            // Remove duplicate avatar column (keep avatar_url)
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
            
            // Remove duplicate birth_date column (keep date_of_birth)
            if (Schema::hasColumn('users', 'birth_date')) {
                $table->dropColumn('birth_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-add the columns if needed for rollback
            $table->string('avatar')->nullable();
            $table->date('birth_date')->nullable();
        });
    }
};
