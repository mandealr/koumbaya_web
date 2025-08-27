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
            // Profile picture
            $table->string('avatar_url')->nullable()->after('phone');
            
            // Personal information
            $table->date('birth_date')->nullable()->after('avatar_url');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('birth_date');
            $table->text('bio')->nullable()->after('gender');
            
            // Preferences JSON storage
            $table->json('preferences')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_url',
                'birth_date', 
                'gender',
                'bio',
                'preferences'
            ]);
        });
    }
};
