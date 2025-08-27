<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 5)->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            // Index pour performance
            $table->index('code');
            $table->index('is_active');
            $table->index('is_default');
        });
        
        // Données par défaut
        DB::table('languages')->insert([
            [
                'name' => 'Français',
                'code' => 'fr',
                'is_active' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'English',
                'code' => 'en',
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};