<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 100);
            $table->string('otp_code', 10);
            $table->enum('type', ['email', 'phone']);
            $table->enum('purpose', ['verification', 'reset_password', 'login', 'transaction']);
            $table->integer('attempts')->default(0);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Index pour performance
            $table->index('identifier');
            $table->index('otp_code');
            $table->index('type');
            $table->index('purpose');
            $table->index('is_used');
            $table->index('expires_at');
            
            // Index composé
            $table->index(['identifier', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};