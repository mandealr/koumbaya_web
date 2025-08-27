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
            // Type de compte
            $table->enum('account_type', ['personal', 'business'])->default('personal')->after('user_type_id');
            
            // Informations personnelles étendues
            $table->string('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->decimal('latitude', 10, 8)->nullable()->after('city');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            
            // Informations business
            $table->string('business_name')->nullable()->after('longitude');
            $table->string('business_email')->nullable()->after('business_name');
            $table->text('business_description')->nullable()->after('business_email');
            
            // Statut hybride
            $table->boolean('can_sell')->default(true)->after('business_description');
            $table->boolean('can_buy')->default(true)->after('can_sell');
            
            // Rating et réputation
            $table->decimal('rating', 3, 2)->default(0.00)->after('can_buy'); // Note sur 5
            $table->integer('rating_count')->default(0)->after('rating');
            
            // Authentification sociale
            $table->string('facebook_id')->nullable()->after('rating_count');
            $table->string('google_id')->nullable()->after('facebook_id');
            $table->string('apple_id')->nullable()->after('google_id');
            
            // Préférences
            $table->boolean('email_notifications')->default(true)->after('apple_id');
            $table->boolean('sms_notifications')->default(true)->after('email_notifications');
            $table->boolean('push_notifications')->default(true)->after('sms_notifications');
            
            // Index pour les performances
            $table->index('account_type');
            $table->index('can_sell');
            $table->index('can_buy');
            $table->index('rating');
            $table->index('city');
            $table->index(['latitude', 'longitude']);
            $table->index('facebook_id');
            $table->index('google_id');
            $table->index('apple_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['users_account_type_index']);
            $table->dropIndex(['users_can_sell_index']);
            $table->dropIndex(['users_can_buy_index']);
            $table->dropIndex(['users_rating_index']);
            $table->dropIndex(['users_city_index']);
            $table->dropIndex(['users_latitude_longitude_index']);
            $table->dropIndex(['users_facebook_id_index']);
            $table->dropIndex(['users_google_id_index']);
            $table->dropIndex(['users_apple_id_index']);
            
            $table->dropColumn([
                'account_type', 'address', 'city', 'latitude', 'longitude',
                'business_name', 'business_email', 'business_description',
                'can_sell', 'can_buy', 'rating', 'rating_count',
                'facebook_id', 'google_id', 'apple_id',
                'email_notifications', 'sms_notifications', 'push_notifications'
            ]);
        });
    }
};