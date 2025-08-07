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
        Schema::create('user_login_histories', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedInteger('user_id');
            $table->string('ip_address', 40)->nullable();
            $table->enum('state', ['success', 'failed'])->default('failed');
            $table->timestamps();
            
            $table->primary('id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_login_histories');
    }
};
