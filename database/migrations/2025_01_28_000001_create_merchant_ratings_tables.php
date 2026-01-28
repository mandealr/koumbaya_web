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
        // Table principale des notations marchands (dénormalisée pour performance)
        Schema::create('merchant_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('users')->onDelete('cascade');

            // Scores composites (0-100)
            $table->decimal('overall_score', 5, 2)->default(50.00);
            $table->decimal('activity_score', 5, 2)->default(50.00);
            $table->decimal('quality_score', 5, 2)->default(50.00);
            $table->decimal('reliability_score', 5, 2)->default(50.00);

            // Métriques d'activité
            $table->integer('total_products')->default(0);
            $table->integer('active_products')->default(0);
            $table->integer('completed_sales')->default(0);
            $table->integer('fulfilled_orders')->default(0);
            $table->integer('total_orders')->default(0);
            $table->integer('cancelled_orders')->default(0);

            // Métriques de qualité (avis clients)
            $table->decimal('avg_rating', 3, 2)->default(0.00); // 1.00 - 5.00
            $table->integer('total_reviews')->default(0);
            $table->integer('verified_reviews')->default(0);
            $table->integer('positive_reviews')->default(0); // rating >= 4
            $table->integer('neutral_reviews')->default(0);  // rating = 3
            $table->integer('negative_reviews')->default(0); // rating <= 2

            // Métriques de fiabilité
            $table->integer('total_refunds')->default(0);
            $table->decimal('refund_rate', 5, 2)->default(0.00); // %
            $table->integer('dispute_count')->default(0);
            $table->decimal('dispute_rate', 5, 2)->default(0.00); // %
            $table->integer('admin_warnings')->default(0);

            // Tendance et metadata
            $table->enum('score_trend', ['up', 'stable', 'down'])->default('stable');
            $table->enum('badge', ['excellent', 'very_good', 'good', 'average', 'poor'])->default('average');
            $table->timestamp('last_recalculated_at')->nullable();

            $table->timestamps();

            // Index pour performance
            $table->unique('merchant_id');
            $table->index('overall_score');
            $table->index('avg_rating');
            $table->index('badge');
            $table->index('last_recalculated_at');
        });

        // Historique mensuel des scores (snapshots)
        Schema::create('merchant_rating_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('users')->onDelete('cascade');

            // Scores du mois
            $table->decimal('overall_score', 5, 2);
            $table->decimal('activity_score', 5, 2);
            $table->decimal('quality_score', 5, 2);
            $table->decimal('reliability_score', 5, 2);

            // Métriques du mois
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            $table->integer('completed_sales')->default(0);
            $table->integer('fulfilled_orders')->default(0);
            $table->decimal('refund_rate', 5, 2)->default(0.00);
            $table->integer('dispute_count')->default(0);

            // Période
            $table->date('snapshot_month'); // Premier jour du mois (YYYY-MM-01)

            $table->timestamps();

            // Index
            $table->index(['merchant_id', 'snapshot_month']);
            $table->index('overall_score');
            $table->unique(['merchant_id', 'snapshot_month']);
        });

        // Log des changements de notation (audit trail)
        Schema::create('merchant_rating_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('users')->onDelete('cascade');

            // Changement de score
            $table->decimal('old_score', 5, 2)->nullable();
            $table->decimal('new_score', 5, 2);
            $table->decimal('score_change', 5, 2)->default(0.00);

            // Raison du changement
            $table->string('change_reason', 100); // 'new_review', 'order_fulfilled', 'refund_processed', 'manual_recalc'
            $table->string('related_entity_type', 50)->nullable(); // 'user_rating', 'order', 'refund'
            $table->unsignedBigInteger('related_entity_id')->nullable();

            // Metadata
            $table->string('calculated_by', 50)->default('system'); // 'system', 'admin', 'scheduled'
            $table->json('details')->nullable(); // Détails supplémentaires

            $table->timestamps();

            // Index
            $table->index(['merchant_id', 'created_at']);
            $table->index('change_reason');
        });

        // Ajouter colonnes dénormalisées à la table users pour affichage rapide
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('merchant_score', 5, 2)->nullable()->after('rating_count');
            $table->string('merchant_badge', 20)->nullable()->after('merchant_score');
            $table->timestamp('merchant_score_updated_at')->nullable()->after('merchant_badge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['merchant_score', 'merchant_badge', 'merchant_score_updated_at']);
        });

        Schema::dropIfExists('merchant_rating_logs');
        Schema::dropIfExists('merchant_rating_snapshots');
        Schema::dropIfExists('merchant_ratings');
    }
};
