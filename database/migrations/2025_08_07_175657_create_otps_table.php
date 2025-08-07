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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('identifier'); // Email ou téléphone
            $table->string('code', 6); // Code OTP (6 chiffres)
            $table->enum('type', ['email', 'sms']); // Type d'OTP
            $table->enum('purpose', ['registration', 'password_reset', 'login', 'payment']); // But de l'OTP
            $table->timestamp('expires_at'); // Expiration
            $table->boolean('is_used')->default(false); // Utilisé ou non
            $table->string('ip_address')->nullable(); // IP de la requête
            $table->string('user_agent')->nullable(); // User agent
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['identifier', 'code', 'is_used']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
