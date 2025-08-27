<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->text('description')->nullable();
            $table->string('category', 50)->default('general');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            
            // Index pour performance
            $table->index(['category', 'key']);
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};