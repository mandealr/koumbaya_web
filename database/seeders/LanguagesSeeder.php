<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'name' => 'Français',
                'code' => 'fr',
                'native_name' => 'Français',
                'is_active' => true,
                'is_default' => true
            ],
            [
                'name' => 'English',
                'code' => 'en',
                'native_name' => 'English',
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'Español',
                'code' => 'es',
                'native_name' => 'Español',
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'Português',
                'code' => 'pt',
                'native_name' => 'Português',
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'العربية',
                'code' => 'ar',
                'native_name' => 'العربية',
                'is_active' => false,
                'is_default' => false
            ]
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}