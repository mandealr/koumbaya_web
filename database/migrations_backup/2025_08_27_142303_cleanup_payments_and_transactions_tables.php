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
        // Table payments - Migration des données avant modification de la structure
        Schema::table('payments', function (Blueprint $table) {
            // D'abord, migrer les données existantes vers un champ meta JSON temporaire
            $table->json('meta')->nullable()->after('callback_data');
        });

        // Migrer les données existantes vers le champ meta
        DB::statement("UPDATE payments SET meta = JSON_OBJECT(
            'transaction_id', IFNULL(transaction_id, ''),
            'user_id', IFNULL(user_id, ''),
            'currency', IFNULL(currency, 'XAF'),
            'customer_name', IFNULL(customer_name, ''),
            'customer_phone', IFNULL(customer_phone, ''),
            'customer_email', IFNULL(customer_email, ''),
            'description', IFNULL(description, ''),
            'payment_gateway', IFNULL(payment_gateway, 'ebilling'),
            'gateway_config', IFNULL(gateway_config, '{}'),
            'success_url', IFNULL(success_url, ''),
            'callback_url', IFNULL(callback_url, ''),
            'gateway_response', IFNULL(gateway_response, '{}'),
            'processed_at', IFNULL(processed_at, '')
        )");

        // Ajouter order_id si pas déjà présent
        if (!Schema::hasColumn('payments', 'order_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedInteger('order_id')->nullable()->after('reference');
                $table->index('order_id');
            });
        }

        // Ajouter callback_data si pas déjà présent
        if (!Schema::hasColumn('payments', 'callback_data')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->json('callback_data')->nullable()->after('external_transaction_id');
            });
        }

        // Supprimer les colonnes non nécessaires pour E-Billing
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['payment_gateway']);
            
            $table->dropColumn([
                'transaction_id',
                'user_id',
                'currency',
                'customer_name',
                'customer_phone',
                'customer_email',
                'description',
                'payment_gateway',
                'gateway_config',
                'success_url',
                'callback_url',
                'gateway_response',
                'processed_at'
            ]);
        });

        // Table transactions - Migration des données avant modification
        Schema::table('transactions', function (Blueprint $table) {
            // Ajouter champs manquants
            if (!Schema::hasColumn('transactions', 'order_id')) {
                $table->unsignedInteger('order_id')->nullable()->after('user_id');
                $table->index('order_id');
            }

            if (!Schema::hasColumn('transactions', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('reference');
                $table->index('transaction_id');
            }

            if (!Schema::hasColumn('transactions', 'ebilling_id')) {
                $table->string('ebilling_id')->nullable()->after('external_transaction_id');
                $table->index('ebilling_id');
            }

            if (!Schema::hasColumn('transactions', 'callback_data')) {
                $table->json('callback_data')->nullable()->after('metadata');
            }

            // Ajouter un champ meta pour stocker les anciennes données
            $table->json('meta')->nullable()->after('callback_data');
        });

        // Migrer les données existantes vers meta
        DB::statement("UPDATE transactions SET meta = JSON_OBJECT(
            'type', IFNULL(type, ''),
            'currency', IFNULL(currency, 'XAF'),
            'product_id', IFNULL(product_id, ''),
            'lottery_id', IFNULL(lottery_id, ''),
            'lottery_ticket_id', IFNULL(lottery_ticket_id, ''),
            'description', IFNULL(description, ''),
            'metadata', IFNULL(metadata, '{}')
        )");

        // Supprimer les colonnes non nécessaires
        Schema::table('transactions', function (Blueprint $table) {
            // Supprimer les index d'abord
            $table->dropIndex(['type']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['lottery_id']);
            
            $table->dropColumn([
                'type',
                'currency',
                'product_id',
                'lottery_id',
                'lottery_ticket_id',
                'description',
                'metadata'
            ]);
        });

        // Nettoyer le schéma final de la table payments
        // Champs conservés : id, reference, order_id, ebilling_id, external_transaction_id, 
        // payment_method, amount, status, callback_data, paid_at, meta, created_at, updated_at

        // Nettoyer le schéma final de la table transactions  
        // Champs conservés : id, reference, user_id, order_id, transaction_id, amount,
        // status, payment_method, external_transaction_id, ebilling_id, callback_data,
        // paid_at, meta, created_at, updated_at
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer la table payments
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('transaction_id')->after('reference');
            $table->unsignedInteger('user_id')->after('transaction_id');
            $table->string('currency', 3)->default('XAF')->after('amount');
            $table->string('customer_name')->after('currency');
            $table->string('customer_phone')->after('customer_name');
            $table->string('customer_email')->after('customer_phone');
            $table->text('description')->after('customer_email');
            $table->string('payment_gateway')->default('ebilling')->after('description');
            $table->json('gateway_config')->nullable()->after('payment_gateway');
            $table->string('success_url')->nullable()->after('ebilling_id');
            $table->string('callback_url')->nullable()->after('success_url');
            $table->json('gateway_response')->nullable()->after('external_transaction_id');
            $table->dateTime('processed_at')->nullable()->after('paid_at');

            $table->index('transaction_id');
            $table->index('user_id');
            $table->index('payment_gateway');
        });

        // Restaurer les données depuis meta
        DB::statement("UPDATE payments SET 
            transaction_id = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.transaction_id')),
            user_id = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.user_id')),
            currency = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.currency')),
            customer_name = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.customer_name')),
            customer_phone = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.customer_phone')),
            customer_email = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.customer_email')),
            description = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.description')),
            payment_gateway = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.payment_gateway')),
            gateway_config = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.gateway_config')),
            success_url = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.success_url')),
            callback_url = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.callback_url')),
            gateway_response = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.gateway_response')),
            processed_at = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.processed_at'))
        WHERE meta IS NOT NULL");

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('meta');
            if (Schema::hasColumn('payments', 'order_id')) {
                $table->dropIndex(['order_id']);
                $table->dropColumn('order_id');
            }
        });

        // Restaurer la table transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('type', ['ticket_purchase', 'direct_purchase', 'refund', 'commission'])->after('user_id');
            $table->string('currency', 3)->default('XAF')->after('amount');
            $table->unsignedInteger('product_id')->nullable()->after('external_transaction_id');
            $table->unsignedInteger('lottery_id')->nullable()->after('product_id');
            $table->unsignedInteger('lottery_ticket_id')->nullable()->after('lottery_id');
            $table->text('description')->after('lottery_ticket_id');
            $table->json('metadata')->nullable()->after('description');

            $table->index('type');
            $table->index('product_id');
            $table->index('lottery_id');
        });

        // Restaurer les données depuis meta
        DB::statement("UPDATE transactions SET 
            type = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.type')),
            currency = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.currency')),
            product_id = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.product_id')),
            lottery_id = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.lottery_id')),
            lottery_ticket_id = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.lottery_ticket_id')),
            description = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.description')),
            metadata = JSON_UNQUOTE(JSON_EXTRACT(meta, '$.metadata'))
        WHERE meta IS NOT NULL");

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('meta');
            if (Schema::hasColumn('transactions', 'order_id')) {
                $table->dropIndex(['order_id']);
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('transactions', 'transaction_id')) {
                $table->dropIndex(['transaction_id']);
                $table->dropColumn('transaction_id');
            }
            if (Schema::hasColumn('transactions', 'ebilling_id')) {
                $table->dropIndex(['ebilling_id']);
                $table->dropColumn('ebilling_id');
            }
            if (Schema::hasColumn('transactions', 'callback_data')) {
                $table->dropColumn('callback_data');
            }
        });
    }
};