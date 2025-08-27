<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('session_id')->unique();
            $table->string('device', 100)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('ip_address', 45);
            $table->string('location', 100)->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('last_activity');
            $table->timestamps();
            
            // Index pour performance
            $table->index('user_id');
            $table->index('session_id');
            $table->index(['user_id', 'is_current']);
            $table->index(['user_id', 'last_activity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};