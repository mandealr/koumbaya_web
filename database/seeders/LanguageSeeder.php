<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
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
                'is_default' => true,
            ],
            [
                'name' => 'Anglais',
                'code' => 'en',
                'native_name' => 'English',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Espagnol',
                'code' => 'es',
                'native_name' => 'Español',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Allemand',
                'code' => 'de',
                'native_name' => 'Deutsch',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Italien',
                'code' => 'it',
                'native_name' => 'Italiano',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Portugais',
                'code' => 'pt',
                'native_name' => 'Português',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Arabe',
                'code' => 'ar',
                'native_name' => 'العربية',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Chinois',
                'code' => 'zh',
                'native_name' => '中文',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}