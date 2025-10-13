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
                'flag' => '🇫🇷',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Anglais',
                'code' => 'en',
                'flag' => '🇬🇧',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Espagnol',
                'code' => 'es',
                'flag' => '🇪🇸',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Allemand',
                'code' => 'de',
                'flag' => '🇩🇪',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Italien',
                'code' => 'it',
                'flag' => '🇮🇹',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Portugais',
                'code' => 'pt',
                'flag' => '🇵🇹',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Arabe',
                'code' => 'ar',
                'flag' => '🇸🇦',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Chinois',
                'code' => 'zh',
                'flag' => '🇨🇳',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($languages as $languageData) {
            Language::firstOrCreate(
                ['code' => $languageData['code']],
                $languageData
            );
        }
    }
}