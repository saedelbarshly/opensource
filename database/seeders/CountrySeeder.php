<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\CountryTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
{
    $countries = [
        [
            'ar' => [
                'name'        => 'المملكة العربية السعودية',
                'short_name'  => 'السعودية',
                'nationality' => 'سعودي',
                'currency'    => 'ريال سعودي',
            ],
            'en' => [
                'name'        => 'Saudi Arabia',
                'short_name'  => 'KSA',
                'nationality' => 'Saudi',
                'currency'    => 'Saudi Riyal',
            ],
            'code'                => 'SA',
            'phone_code'          => '966',
            'phone_length'        => 9,
            'national_id_length'  => 10,
            'continent'           => 'asia',
        ],
    ];

    foreach ($countries as $data) {

        $country = Country::create([
            'code'               => $data['code'],
            'phone_code'         => $data['phone_code'],
            'phone_length'       => $data['phone_length'],
            'national_id_length' => $data['national_id_length'],
            'continent'          => $data['continent'],
            'is_active'          => true,
        ]);

        foreach (['ar', 'en'] as $locale) {
            CountryTranslation::create([
                'country_id'  => $country->id,
                'locale'      => $locale,
                'name'        => $data[$locale]['name'],
                'short_name'  => $data[$locale]['short_name'],
                'currency'    => $data[$locale]['currency'],
                'nationality' => $data[$locale]['nationality'],
            ]);
        }
    }
}

}
