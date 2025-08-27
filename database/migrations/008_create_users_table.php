<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->unique();
            $table->string('password', 255);
            
            // Relations
            $table->bigInteger('user_type_id')->unsigned();
            $table->bigInteger('country_id')->unsigned()->nullable();
            $table->bigInteger('language_id')->unsigned()->nullable();
            
            // Profil utilisateur
            $table->string('avatar', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['M', 'F', 'O'])->nullable();
            $table->text('bio')->nullable();
            
            // Informations entreprise (pour marchands)
            $table->string('company_name', 255)->nullable();
            $table->string('company_registration', 100)->nullable();
            $table->string('tax_id', 100)->nullable();
            
            // Adresse
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            
            // Paiements
            $table->text('bank_details')->nullable();
            $table->string('payment_phone', 20)->nullable();
            $table->string('preferred_payment_method', 50)->nullable();
            
            // Préférences et statuts
            $table->string('notification_preferences', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_phone_verified')->default(false);
            $table->boolean('is_email_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_login_date')->nullable();
            $table->timestamp('last_otp_request')->nullable();
            
            // Sécurité et tracking
            $table->string('source_ip_address', 45)->nullable();
            $table->text('source_server_info')->nullable();
            $table->boolean('mfa_is_active')->default(false);
            $table->text('google2fa_secret')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            
            // Index pour performance
            $table->index('email');
            $table->index('phone');
            $table->index('user_type_id');
            $table->index('country_id');
            $table->index('language_id');
            $table->index('is_active');
            $table->index('is_phone_verified');
            $table->index('is_email_verified');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};