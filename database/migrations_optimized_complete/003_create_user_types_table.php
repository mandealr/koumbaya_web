<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index pour performance
            $table->index('code');
            $table->index('is_active');
        });
        
        // Données par défaut
        DB::table('user_types')->insert([
            [
                'name' => 'Marchand',
                'code' => 'merchant',
                'description' => 'Utilisateur marchand qui peut vendre des produits',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Client',
                'code' => 'customer',
                'description' => 'Client qui achète des produits et participe aux loteries',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administrateur',
                'code' => 'admin',
                'description' => 'Administrateur du système',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_types');
    }
};