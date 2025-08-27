<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 3)->unique();
            $table->string('phone_code', 10);
            $table->string('flag', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Index pour performance
            $table->index('code');
            $table->index('is_active');
            $table->index('sort_order');
        });
        
        // Données par défaut - Gabon
        DB::table('countries')->insert([
            'name' => 'Gabon',
            'code' => 'GA',
            'phone_code' => '+241',
            'flag' => '🇬🇦',
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};