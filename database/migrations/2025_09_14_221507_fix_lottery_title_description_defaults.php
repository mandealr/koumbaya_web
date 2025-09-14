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
            // Modify title column to allow NULL and add default value
            $table->string('title')->nullable()->default('Tombola')->change();
            
            // Modify description column to allow NULL (TEXT columns can't have default values in MySQL)
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lotteries', function (Blueprint $table) {
            // Restore original constraints (not nullable, no default)
            $table->string('title')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });
    }
};