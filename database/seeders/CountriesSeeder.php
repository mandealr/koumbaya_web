<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Gabon',
                'iso_code_2' => 'GA',
                'iso_code_3' => 'GAB',
                'phone_code' => '+241',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => '🇬🇦',
                'is_active' => true
            ],
            [
                'name' => 'France',
                'iso_code_2' => 'FR',
                'iso_code_3' => 'FRA',
                'phone_code' => '+33',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'flag' => '🇫🇷',
                'is_active' => true
            ],
            [
                'name' => 'Cameroun',
                'iso_code_2' => 'CM',
                'iso_code_3' => 'CMR',
                'phone_code' => '+237',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => '🇨🇲',
                'is_active' => true
            ],
            [
                'name' => 'Côte d\'Ivoire',
                'iso_code_2' => 'CI',
                'iso_code_3' => 'CIV',
                'phone_code' => '+225',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => '🇨🇮',
                'is_active' => true
            ],
            [
                'name' => 'Sénégal',
                'iso_code_2' => 'SN',
                'iso_code_3' => 'SEN',
                'phone_code' => '+221',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => '🇸🇳',
                'is_active' => true
            ],
            [
                'name' => 'Mali',
                'iso_code_2' => 'ML',
                'iso_code_3' => 'MLI',
                'phone_code' => '+223',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => '🇲🇱',
                'is_active' => true
            ],
            [
                'name' => 'Burkina Faso',
                'iso_code_2' => 'BF',
                'iso_code_3' => 'BFA',
                'phone_code' => '+226',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => '🇧🇫',
                'is_active' => true
            ],
            [
                'name' => 'Guinée Équatoriale',
                'iso_code_2' => 'GQ',
                'iso_code_3' => 'GNQ',
                'phone_code' => '+240',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => '🇬🇶',
                'is_active' => true
            ],
            [
                'name' => 'République Centrafricaine',
                'iso_code_2' => 'CF',
                'iso_code_3' => 'CAF',
                'phone_code' => '+236',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => '🇨🇫',
                'is_active' => true
            ],
            [
                'name' => 'Tchad',
                'iso_code_2' => 'TD',
                'iso_code_3' => 'TCD',
                'phone_code' => '+235',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => '🇹🇩',
                'is_active' => true
            ]
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['iso_code_2' => $country['iso_code_2']],
                $country
            );
        }
    }
}