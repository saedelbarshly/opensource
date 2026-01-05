<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityTranslation;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Saudi Arabia country
        $saudi = Country::where('code', 'SA')->firstOrFail();

        $cities = [
            ['ar' => 'الرياض',        'en' => 'Riyadh'],
            ['ar' => 'جدة',           'en' => 'Jeddah'],
            ['ar' => 'مكة المكرمة',   'en' => 'Makkah'],
            ['ar' => 'المدينة المنورة', 'en' => 'Madinah'],
            ['ar' => 'الدمام',        'en' => 'Dammam'],
            ['ar' => 'الخبر',         'en' => 'Khobar'],
            ['ar' => 'الظهران',       'en' => 'Dhahran'],
            ['ar' => 'الأحساء',       'en' => 'Al Ahsa'],
            ['ar' => 'الطائف',        'en' => 'Taif'],
            ['ar' => 'بريدة',         'en' => 'Buraidah'],
            ['ar' => 'عنيزة',         'en' => 'Unaizah'],
            ['ar' => 'حائل',          'en' => 'Hail'],
            ['ar' => 'أبها',          'en' => 'Abha'],
            ['ar' => 'خميس مشيط',     'en' => 'Khamis Mushait'],
            ['ar' => 'جازان',         'en' => 'Jazan'],
            ['ar' => 'نجران',         'en' => 'Najran'],
            ['ar' => 'الباحة',        'en' => 'Al Baha'],
            ['ar' => 'سكاكا',         'en' => 'Sakaka'],
            ['ar' => 'عرعر',          'en' => 'Arar'],
            ['ar' => 'تبوك',          'en' => 'Tabuk'],
            ['ar' => 'ينبع',          'en' => 'Yanbu'],
            ['ar' => 'الجبيل',        'en' => 'Jubail'],
            ['ar' => 'القنفذة',       'en' => 'Al Qunfudhah'],
        ];

        foreach ($cities as $data) {
            
            $city = City::create([
                'country_id' => $saudi->id,
                'is_active'  => true,
            ]);

            foreach (['ar', 'en'] as $locale) {
                CityTranslation::create([
                    'city_id' => $city->id,
                    'locale'  => $locale,
                    'name'    => $data[$locale],
                ]);
            }
        }
    }
}
