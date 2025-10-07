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
            $table->string('google_id')->nullable()->unique()->after('password');
            $table->string('facebook_id')->nullable()->unique()->after('google_id');
            $table->string('apple_id')->nullable()->unique()->after('facebook_id');

            // Index pour performance
            $table->index('google_id');
            $table->index('facebook_id');
            $table->index('apple_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['google_id']);
            $table->dropIndex(['facebook_id']);
            $table->dropIndex(['apple_id']);

            $table->dropColumn(['google_id', 'facebook_id', 'apple_id']);
        });
    }
};
