<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
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
                'flag' => 'ga.png',
                'is_active' => true,
            ],
            [
                'name' => 'Cameroun',
                'iso_code_2' => 'CM',
                'iso_code_3' => 'CMR',
                'phone_code' => '+237',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => 'cm.png',
                'is_active' => true,
            ],
            [
                'name' => 'Sénégal',
                'iso_code_2' => 'SN',
                'iso_code_3' => 'SEN',
                'phone_code' => '+221',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => 'sn.png',
                'is_active' => true,
            ],
            [
                'name' => 'Côte d\'Ivoire',
                'iso_code_2' => 'CI',
                'iso_code_3' => 'CIV',
                'phone_code' => '+225',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => 'ci.png',
                'is_active' => true,
            ],
            [
                'name' => 'Burkina Faso',
                'iso_code_2' => 'BF',
                'iso_code_3' => 'BFA',
                'phone_code' => '+226',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => 'bf.png',
                'is_active' => true,
            ],
            [
                'name' => 'Mali',
                'iso_code_2' => 'ML',
                'iso_code_3' => 'MLI',
                'phone_code' => '+223',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => 'ml.png',
                'is_active' => true,
            ],
            [
                'name' => 'Niger',
                'iso_code_2' => 'NE',
                'iso_code_3' => 'NER',
                'phone_code' => '+227',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => 'ne.png',
                'is_active' => true,
            ],
            [
                'name' => 'Tchad',
                'iso_code_2' => 'TD',
                'iso_code_3' => 'TCD',
                'phone_code' => '+235',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => 'td.png',
                'is_active' => true,
            ],
            [
                'name' => 'République Centrafricaine',
                'iso_code_2' => 'CF',
                'iso_code_3' => 'CAF',
                'phone_code' => '+236',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => 'cf.png',
                'is_active' => true,
            ],
            [
                'name' => 'Guinée Équatoriale',
                'iso_code_2' => 'GQ',
                'iso_code_3' => 'GNQ',
                'phone_code' => '+240',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => 'gq.png',
                'is_active' => true,
            ],
            [
                'name' => 'République du Congo',
                'iso_code_2' => 'CG',
                'iso_code_3' => 'COG',
                'phone_code' => '+242',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'flag' => 'cg.png',
                'is_active' => true,
            ],
            [
                'name' => 'République Démocratique du Congo',
                'iso_code_2' => 'CD',
                'iso_code_3' => 'COD',
                'phone_code' => '+243',
                'currency_code' => 'CDF',
                'currency_symbol' => 'FC',
                'flag' => 'cd.png',
                'is_active' => true,
            ],
            [
                'name' => 'Bénin',
                'iso_code_2' => 'BJ',
                'iso_code_3' => 'BEN',
                'phone_code' => '+229',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => 'bj.png',
                'is_active' => true,
            ],
            [
                'name' => 'Togo',
                'iso_code_2' => 'TG',
                'iso_code_3' => 'TGO',
                'phone_code' => '+228',
                'currency_code' => 'XOF',
                'currency_symbol' => 'FCFA',
                'flag' => 'tg.png',
                'is_active' => true,
            ],
            [
                'name' => 'Guinée',
                'iso_code_2' => 'GN',
                'iso_code_3' => 'GIN',
                'phone_code' => '+224',
                'currency_code' => 'GNF',
                'currency_symbol' => 'FG',
                'flag' => 'gn.png',
                'is_active' => true,
            ],
            [
                'name' => 'Madagascar',
                'iso_code_2' => 'MG',
                'iso_code_3' => 'MDG',
                'phone_code' => '+261',
                'currency_code' => 'MGA',
                'currency_symbol' => 'Ar',
                'flag' => 'mg.png',
                'is_active' => true,
            ],
            [
                'name' => 'Burundi',
                'iso_code_2' => 'BI',
                'iso_code_3' => 'BDI',
                'phone_code' => '+257',
                'currency_code' => 'BIF',
                'currency_symbol' => 'FBu',
                'flag' => 'bi.png',
                'is_active' => true,
            ],
            [
                'name' => 'Rwanda',
                'iso_code_2' => 'RW',
                'iso_code_3' => 'RWA',
                'phone_code' => '+250',
                'currency_code' => 'RWF',
                'currency_symbol' => 'FRw',
                'flag' => 'rw.png',
                'is_active' => true,
            ],
            [
                'name' => 'Djibouti',
                'iso_code_2' => 'DJ',
                'iso_code_3' => 'DJI',
                'phone_code' => '+253',
                'currency_code' => 'DJF',
                'currency_symbol' => 'Fdj',
                'flag' => 'dj.png',
                'is_active' => true,
            ],
            [
                'name' => 'Comores',
                'iso_code_2' => 'KM',
                'iso_code_3' => 'COM',
                'phone_code' => '+269',
                'currency_code' => 'KMF',
                'currency_symbol' => 'CF',
                'flag' => 'km.png',
                'is_active' => true,
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['iso_code_2' => $country['iso_code_2']],
                $country
            );
        }
    }
}