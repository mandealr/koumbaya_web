<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_id', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->enum('category', ['user', 'admin', 'merchant'])->default('user');
            $table->timestamps();
            
            // Index pour performance
            $table->index('type_id');
            $table->index('category');
            $table->index('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};