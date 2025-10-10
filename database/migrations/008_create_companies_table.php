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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('business_email')->nullable();
            $table->text('business_description')->nullable();
            $table->string('tax_id')->nullable()->unique(); // Numéro fiscal/SIRET
            $table->string('registration_number')->nullable(); // Numéro d'immatriculation
            $table->string('vat_number')->nullable(); // Numéro TVA
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_url')->nullable();
            $table->enum('company_type', ['individual', 'enterprise'])->default('enterprise'); // business_individual ou business_enterprise
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Pour infos supplémentaires
            $table->timestamps();
            $table->softDeletes();

            // Index pour recherche et performance
            $table->index('business_name');
            $table->index('business_email');
            $table->index('tax_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
