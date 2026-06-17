<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute les contraintes de clé étrangère manquantes sur l'axe métier/financier
 * (intégrité référentielle aujourd'hui absente en base).
 *
 * MySQL uniquement : SQLite ne supporte pas l'ajout de FK via ALTER TABLE
 * (la suite de tests tourne sur SQLite et saute donc cette migration).
 *
 * Défensif : les orphelins des colonnes nullables sont remis à NULL avant
 * l'ajout ; pour les colonnes requises, la FK est ignorée (et journalisée) si
 * des orphelins existent, afin de ne jamais faire échouer un déploiement.
 */
return new class extends Migration
{
    /**
     * @var array<int, array{child:string, column:string, parent:string, nullable:bool}>
     */
    private array $foreignKeys = [
        // Requises
        ['child' => 'orders',          'column' => 'user_id',        'parent' => 'users',      'nullable' => false],
        ['child' => 'payments',        'column' => 'order_id',       'parent' => 'orders',     'nullable' => false],
        ['child' => 'payments',        'column' => 'user_id',        'parent' => 'users',      'nullable' => false],
        ['child' => 'lottery_tickets', 'column' => 'lottery_id',     'parent' => 'lotteries',  'nullable' => false],
        ['child' => 'lottery_tickets', 'column' => 'user_id',        'parent' => 'users',      'nullable' => false],
        ['child' => 'refunds',         'column' => 'user_id',        'parent' => 'users',      'nullable' => false],
        ['child' => 'lotteries',       'column' => 'product_id',     'parent' => 'products',   'nullable' => false],
        ['child' => 'products',        'column' => 'category_id',    'parent' => 'categories', 'nullable' => false],
        ['child' => 'products',        'column' => 'merchant_id',    'parent' => 'users',      'nullable' => false],
        // Nullables
        ['child' => 'orders',          'column' => 'product_id',     'parent' => 'products',   'nullable' => true],
        ['child' => 'orders',          'column' => 'lottery_id',     'parent' => 'lotteries',  'nullable' => true],
        ['child' => 'payments',        'column' => 'lottery_id',     'parent' => 'lotteries',  'nullable' => true],
        ['child' => 'payments',        'column' => 'product_id',     'parent' => 'products',   'nullable' => true],
        ['child' => 'lottery_tickets', 'column' => 'order_id',       'parent' => 'orders',     'nullable' => true],
        ['child' => 'lottery_tickets', 'column' => 'payment_id',     'parent' => 'payments',   'nullable' => true],
        ['child' => 'refunds',         'column' => 'order_id',       'parent' => 'orders',     'nullable' => true],
        ['child' => 'refunds',         'column' => 'payment_id',     'parent' => 'payments',   'nullable' => true],
        ['child' => 'lotteries',       'column' => 'winner_user_id', 'parent' => 'users',      'nullable' => true],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->foreignKeys as $fk) {
            $this->addForeignKey($fk['child'], $fk['column'], $fk['parent'], $fk['nullable']);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->foreignKeys as $fk) {
            try {
                Schema::table($fk['child'], function ($table) use ($fk) {
                    $table->dropForeign([$fk['column']]);
                });
            } catch (\Throwable $e) {
                // FK absente : on ignore
            }
        }
    }

    private function addForeignKey(string $child, string $column, string $parent, bool $nullable): void
    {
        // Compter les orphelins (valeur non nulle sans parent correspondant).
        $orphans = DB::table($child)
            ->whereNotNull($column)
            ->whereNotIn($column, function ($q) use ($parent) {
                $q->select('id')->from($parent);
            })
            ->count();

        if ($orphans > 0) {
            if ($nullable) {
                // Remettre les références orphelines à NULL avant d'ajouter la FK.
                DB::table($child)
                    ->whereNotNull($column)
                    ->whereNotIn($column, function ($q) use ($parent) {
                        $q->select('id')->from($parent);
                    })
                    ->update([$column => null]);

                Log::warning("FK {$child}.{$column} -> {$parent}: {$orphans} orphelin(s) remis à NULL avant ajout de la contrainte");
            } else {
                Log::error("FK {$child}.{$column} -> {$parent}: {$orphans} orphelin(s) sur colonne requise — contrainte NON ajoutée (à corriger manuellement)");
                return;
            }
        }

        try {
            Schema::table($child, function ($table) use ($column, $parent, $nullable) {
                $fk = $table->foreign($column)->references('id')->on($parent);
                $nullable ? $fk->nullOnDelete() : $fk->restrictOnDelete();
            });
        } catch (\Throwable $e) {
            // FK déjà existante ou incompatibilité : on journalise sans bloquer.
            Log::warning("FK {$child}.{$column} -> {$parent}: non ajoutée ({$e->getMessage()})");
        }
    }
};
