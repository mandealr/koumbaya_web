<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_login_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('ip_address', 45)->nullable();
            $table->enum('state', ['success', 'failed'])->default('failed');
            $table->timestamps();
            
            // Index pour performance
            $table->index('user_id');
            $table->index('state');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_histories');
    }
};