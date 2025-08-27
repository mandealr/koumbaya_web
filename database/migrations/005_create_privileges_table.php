<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privileges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description', 100);
            $table->bigInteger('user_type_id')->unsigned()->nullable();
            $table->timestamps();
            
            // Index pour performance
            $table->index('user_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privileges');
    }
};