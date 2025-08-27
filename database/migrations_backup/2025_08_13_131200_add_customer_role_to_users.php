<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier la colonne enum pour ajouter 'CUSTOMER'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('MANAGER', 'MERCHANT', 'RESELLER', 'PARTNER', 'CUSTOMER') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre l'enum original (attention: cette opération peut échouer s'il y a des données CUSTOMER)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('MANAGER', 'MERCHANT', 'RESELLER', 'PARTNER') NULL");
    }
};