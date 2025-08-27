<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_privileges', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('privilege_id')->unsigned();
            $table->bigInteger('role_id')->unsigned();
            $table->timestamps();
            
            // Index pour performance et éviter doublons
            $table->index('privilege_id');
            $table->index('role_id');
            $table->unique(['privilege_id', 'role_id'], 'unique_role_privilege');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_privileges');
    }
};