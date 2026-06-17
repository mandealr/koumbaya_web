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
        if (!Schema::hasColumn('payments', 'external_transaction_id')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            // Supprimer l'index avant la colonne (requis par SQLite lors de la
            // reconstruction de table ; no-op si l'index n'existe pas).
            try {
                $table->dropIndex('payments_external_transaction_id_index');
            } catch (\Throwable $e) {
                // index absent : on ignore
            }
            $table->dropColumn('external_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('external_transaction_id')->nullable()->after('transaction_id');
        });
    }
};
