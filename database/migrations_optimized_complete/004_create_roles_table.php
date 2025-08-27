<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 70)->unique();
            $table->string('description', 100);
            $table->boolean('active')->default(true);
            $table->bigInteger('user_type_id')->unsigned()->nullable();
            $table->boolean('mutable')->default(true);
            $table->bigInteger('merchant_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->timestamps();
            
            // Index pour performance
            $table->index('user_type_id');
            $table->index('merchant_id');
            $table->index('user_id');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};