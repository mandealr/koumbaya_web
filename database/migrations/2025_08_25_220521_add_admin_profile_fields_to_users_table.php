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
            $table->string('position')->nullable()->after('bio');
            $table->string('timezone')->default('Africa/Libreville')->after('position');
            $table->string('language')->default('fr')->after('timezone');
            $table->boolean('two_factor_enabled')->default(false)->after('language');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'position',
                'timezone',
                'language',
                'two_factor_enabled',
                'two_factor_secret',
                'last_login_at'
            ]);
        });
    }
};
