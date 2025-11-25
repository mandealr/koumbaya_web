<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name')->nullable();
        });

        // Générer les slugs pour les produits existants
        DB::table('products')->whereNull('slug')->orderBy('id')->chunk(100, function ($products) {
            foreach ($products as $product) {
                $slug = Str::slug($product->name);
                $originalSlug = $slug;
                $counter = 1;

                // Vérifier l'unicité et ajouter un suffixe si nécessaire
                while (DB::table('products')->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                DB::table('products')->where('id', $product->id)->update(['slug' => $slug]);
            }
        });

        // Rendre le slug NOT NULL après avoir rempli tous les slugs
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
