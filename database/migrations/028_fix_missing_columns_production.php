<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Corriger la table refunds si les colonnes sont manquantes
        if (!Schema::hasColumn('refunds', 'auto_processed')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->boolean('auto_processed')->default(false)->after('description');
                $table->index('auto_processed');
            });
        }

        if (!Schema::hasColumn('refunds', 'transaction_id')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->bigInteger('transaction_id')->unsigned()->nullable()->after('user_id');
                $table->index('transaction_id');
            });
        }

        if (!Schema::hasColumn('refunds', 'lottery_id')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->bigInteger('lottery_id')->unsigned()->nullable()->after('transaction_id');
                $table->index('lottery_id');
            });
        }

        if (!Schema::hasColumn('refunds', 'type')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->enum('type', ['automatic', 'manual', 'system'])->default('manual')->after('reason');
                $table->index('type');
            });
        }

        // Corriger la table payments si lottery_id est manquant
        if (!Schema::hasColumn('payments', 'lottery_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->bigInteger('lottery_id')->unsigned()->nullable()->after('order_id');
                $table->index('lottery_id');
            });
        }

        if (!Schema::hasColumn('payments', 'product_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->bigInteger('product_id')->unsigned()->nullable()->after('lottery_id');
                $table->index('product_id');
            });
        }

        // Ajouter les autres colonnes manquantes de refunds si nécessaires
        $refundsColumns = [
            'processed_by' => function($table) {
                $table->bigInteger('processed_by')->unsigned()->nullable();
                $table->index('processed_by');
            },
            'external_refund_id' => function($table) {
                $table->string('external_refund_id', 100)->nullable();
            },
            'callback_data' => function($table) {
                $table->json('callback_data')->nullable();
            },
            'notes' => function($table) {
                $table->text('notes')->nullable();
            },
            'rejected_at' => function($table) {
                $table->timestamp('rejected_at')->nullable();
            },
            'rejected_by' => function($table) {
                $table->bigInteger('rejected_by')->unsigned()->nullable();
                $table->index('rejected_by');
            },
            'rejection_reason' => function($table) {
                $table->text('rejection_reason')->nullable();
            }
        ];

        foreach ($refundsColumns as $column => $definition) {
            if (!Schema::hasColumn('refunds', $column)) {
                Schema::table('refunds', function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            }
        }
    }

    public function down(): void
    {
        // Ne pas supprimer les colonnes en rollback pour éviter la perte de données
    }
};