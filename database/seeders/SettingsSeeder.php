<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // General settings
            [
                'key' => 'app_name',
                'value' => 'Koumbaya',
                'type' => 'string',
                'category' => 'general'
            ],
            [
                'key' => 'app_url',
                'value' => 'https://koumbaya.com',
                'type' => 'string',
                'category' => 'general'
            ],
            [
                'key' => 'app_description',
                'value' => 'Plateforme de tombola et loterie en ligne au Gabon',
                'type' => 'string',
                'category' => 'general'
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@koumbaya.com',
                'type' => 'string',
                'category' => 'general'
            ],
            [
                'key' => 'contact_phone',
                'value' => '+241 01 23 45 67',
                'type' => 'string',
                'category' => 'general'
            ],
            [
                'key' => 'default_language',
                'value' => 'fr',
                'type' => 'string',
                'category' => 'general'
            ],
            [
                'key' => 'default_country',
                'value' => 'GA',
                'type' => 'string',
                'category' => 'general'
            ],

            // Payment settings
            [
                'key' => 'default_currency',
                'value' => 'XAF',
                'type' => 'string',
                'category' => 'payment'
            ],
            [
                'key' => 'platform_commission',
                'value' => 5,
                'type' => 'number',
                'category' => 'payment'
            ],

            // Lottery settings
            [
                'key' => 'min_ticket_price',
                'value' => 500,
                'type' => 'number',
                'category' => 'lottery'
            ],
            [
                'key' => 'max_ticket_price',
                'value' => 50000,
                'type' => 'number',
                'category' => 'lottery'
            ],
            [
                'key' => 'min_participants',
                'value' => 10,
                'type' => 'number',
                'category' => 'lottery'
            ],
            [
                'key' => 'max_duration_days',
                'value' => 30,
                'type' => 'number',
                'category' => 'lottery'
            ],
            [
                'key' => 'auto_refund',
                'value' => true,
                'type' => 'boolean',
                'category' => 'lottery'
            ],
            [
                'key' => 'auto_draw',
                'value' => false,
                'type' => 'boolean',
                'category' => 'lottery'
            ],

            // Notification settings
            [
                'key' => 'from_email',
                'value' => 'noreply@koumbaya.com',
                'type' => 'string',
                'category' => 'notifications'
            ],
            [
                'key' => 'from_name',
                'value' => 'Koumbaya Team',
                'type' => 'string',
                'category' => 'notifications'
            ],

            // Maintenance settings
            [
                'key' => 'maintenance_mode',
                'value' => false,
                'type' => 'boolean',
                'category' => 'maintenance'
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'Nous effectuons une maintenance technique. Nous serons de retour bientôt !',
                'type' => 'string',
                'category' => 'maintenance'
            ]
        ];

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}