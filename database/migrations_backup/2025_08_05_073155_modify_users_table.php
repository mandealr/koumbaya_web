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
        Schema::table('users', function (Blueprint $table) {
            // Vérifier et supprimer les colonnes existantes si elles existent
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
            
            // Ajouter les nouvelles colonnes seulement si elles n'existent pas
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->after('id');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->after('first_name');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['MANAGER', 'MERCHANT', 'RESELLER', 'PARTNER'])->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'last_login_date')) {
                $table->dateTime('last_login_date')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'verified_at')) {
                $table->dateTime('verified_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('users', 'source_ip_address')) {
                $table->string('source_ip_address', 40)->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('users', 'source_server_info')) {
                $table->text('source_server_info')->nullable()->after('source_ip_address');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('source_server_info');
            }
            if (!Schema::hasColumn('users', 'mfa_is_active')) {
                $table->boolean('mfa_is_active')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'google2fa_secret')) {
                $table->text('google2fa_secret')->nullable()->after('mfa_is_active');
            }
            if (!Schema::hasColumn('users', 'user_type_id')) {
                $table->unsignedInteger('user_type_id')->nullable()->after('google2fa_secret');
                $table->index('user_type_id');
            }
            if (!Schema::hasColumn('users', 'last_otp_request')) {
                $table->timestamp('last_otp_request')->nullable()->after('user_type_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restaurer les colonnes Laravel originales
            $table->string('name');
            $table->timestamp('email_verified_at')->nullable();
            
            // Supprimer les colonnes ajoutées
            $table->dropColumn([
                'first_name', 'last_name', 'phone', 'role', 'last_login_date',
                'verified_at', 'source_ip_address', 'source_server_info',
                'is_active', 'mfa_is_active', 'google2fa_secret', 'user_type_id',
                'last_otp_request'
            ]);
            
            $table->dropIndex(['user_type_id']);
        });
    }
};
