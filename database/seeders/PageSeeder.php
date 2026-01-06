<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'type' => 'about',
                'ordering' => 1,
                'translations' => [
                    'en' => [
                        'title' => 'About Us',
                        'content' => 'This is the about page content in English.',
                    ],
                    'ar' => [
                        'title' => 'من نحن',
                        'content' => 'هذا هو محتوى صفحة من نحن باللغة العربية.',
                    ],
                ],
            ],
            [
                'type' => 'privacy',
                'ordering' => 2,
                'translations' => [
                    'en' => [
                        'title' => 'Privacy Policy',
                        'content' => 'This is the privacy policy in English.',
                    ],
                    'ar' => [
                        'title' => 'سياسة الخصوصية',
                        'content' => 'هذه هي سياسة الخصوصية باللغة العربية.',
                    ],
                ],
            ],
            [
                'type' => 'terms',
                'ordering' => 3,
                'translations' => [
                    'en' => [
                        'title' => 'Terms and Conditions',
                        'content' => 'These are the terms and conditions in English.',
                    ],
                    'ar' => [
                        'title' => 'الشروط والأحكام',
                        'content' => 'هذه هي الشروط والأحكام باللغة العربية.',
                    ],
                ],
            ],
             [
                'type' => 'contact',
                'ordering' => 4,
                'translations' => [
                    'en' => [
                        'title' => 'Contact Us',
                        'content' => 'This is the contact page content in English.',
                    ],
                    'ar' => [
                        'title' => 'اتصل بنا',
                        'content' => 'هذا هو محتوى صفحة اتصل بنا باللغة العربية.',
                    ],
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            $pageId = DB::table('pages')->insertGetId([
                'type' => $pageData['type'],
                'ordering' => $pageData['ordering'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($pageData['translations'] as $locale => $translation) {
                DB::table('page_translations')->insert([
                    'page_id' => $pageId,
                    'title' => $translation['title'],
                    'content' => $translation['content'],
                    'locale' => $locale,
                ]);
            }
        }
    }
}
