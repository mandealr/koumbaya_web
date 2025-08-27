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
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Anglais',
                'code' => 'en',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Espagnol',
                'code' => 'es',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Allemand',
                'code' => 'de',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Italien',
                'code' => 'it',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Portugais',
                'code' => 'pt',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Arabe',
                'code' => 'ar',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Chinois',
                'code' => 'zh',
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