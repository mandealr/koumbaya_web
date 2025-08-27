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
        Schema::create('roles', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('name', 70)->unique();
            $table->string('description', 100);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('user_type_id')->nullable();
            $table->boolean('mutable')->default(true);
            $table->unsignedInteger('merchant_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();
            
            $table->primary('id');
            $table->index('user_type_id');
            $table->index('merchant_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
