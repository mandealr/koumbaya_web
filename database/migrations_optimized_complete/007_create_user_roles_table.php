<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('role_id')->unsigned();
            $table->timestamps();
            
            // Index pour performance et éviter doublons
            $table->index('user_id');
            $table->index('role_id');
            $table->unique(['user_id', 'role_id'], 'unique_user_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};