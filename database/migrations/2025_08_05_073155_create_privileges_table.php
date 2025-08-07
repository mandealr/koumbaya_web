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
        Schema::create('privileges', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('name', 100)->unique();
            $table->string('description', 100);
            $table->unsignedInteger('user_type_id')->nullable();
            $table->timestamps();
            
            $table->primary('id');
            $table->index('user_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privileges');
    }
};
