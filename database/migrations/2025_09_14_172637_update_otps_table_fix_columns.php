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
        Schema::table('otps', function (Blueprint $table) {
            // Renommer la colonne otp_code en code
            $table->renameColumn('otp_code', 'code');
            
            // Modifier les enum values pour correspondre au modèle
            // Pour type: 'phone' -> 'sms'
            $table->enum('type', ['email', 'sms'])->change();
            
            // Pour purpose: aligner avec les constantes du modèle
            $table->enum('purpose', ['registration', 'password_reset', 'login', 'payment'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            // Restaurer les changements
            $table->renameColumn('code', 'otp_code');
            $table->enum('type', ['email', 'phone'])->change();
            $table->enum('purpose', ['verification', 'reset_password', 'login', 'transaction'])->change();
        });
    }
};
