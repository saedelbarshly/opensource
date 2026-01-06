<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $categories = [
                // ===============================
                // 1️⃣ Heavy Industries & Construction
                // ===============================
                [
                    'ar' => 'الصناعات الثقيلة ومواد البناء',
                    'en' => 'Heavy Industries & Construction',
                    'subs' => [
                        ['ar' => 'الحديد', 'en' => 'Steel'],
                        ['ar' => 'الأسمنت', 'en' => 'Cement'],
                        ['ar' => 'البلوك والطوب والخرسانة', 'en' => 'Blocks & Concrete'],
                        ['ar' => 'الدهانات والعزل', 'en' => 'Paints & Insulation'],
                        ['ar' => 'الأخشاب المصنعة', 'en' => 'Manufactured Wood'],
                        ['ar' => 'مواد السباكة', 'en' => 'Plumbing Materials'],
                        ['ar' => 'مواد كهربائية', 'en' => 'Electrical Materials'],
                        ['ar' => 'العوازل الحرارية', 'en' => 'Thermal Insulation'],
                    ],
                ],

                // ===============================
                // 2️⃣ Productive Families & Crafts
                // ===============================
                [
                    'ar' => 'منتجات الأسر المنتِجة والحِرَف',
                    'en' => 'Productive Families & Handicrafts',
                    'subs' => [
                        ['ar' => 'منتجات غذائية منزلية', 'en' => 'Homemade Food'],
                        ['ar' => 'حِرف يدوية', 'en' => 'Handicrafts'],
                        ['ar' => 'منسوجات يدوية', 'en' => 'Handmade Textiles'],
                        ['ar' => 'عطور وزيوت طبيعية', 'en' => 'Perfumes & Natural Oils'],
                        ['ar' => 'إكسسوارات يدوية', 'en' => 'Handmade Accessories'],
                        ['ar' => 'شموع وصابون طبيعي', 'en' => 'Natural Soap & Candles'],
                    ],
                ],

                // ===============================
                // 3️⃣ Consumer Industries
                // ===============================
                [
                    'ar' => 'الصناعات الاستهلاكية',
                    'en' => 'Consumer Industries',
                    'subs' => [
                        ['ar' => 'أزياء سعودية', 'en' => 'Saudi Fashion'],
                        ['ar' => 'ملابس جاهزة', 'en' => 'Ready-made Clothing'],
                        ['ar' => 'العناية الشخصية', 'en' => 'Personal Care'],
                        ['ar' => 'أحذية وحقائب جلدية', 'en' => 'Leather Shoes & Bags'],
                        ['ar' => 'أثاث خفيف', 'en' => 'Light Furniture'],
                        ['ar' => 'مفروشات', 'en' => 'Home Furnishings'],
                    ],
                ],

                // ===============================
                // 4️⃣ Food Manufacturing
                // ===============================
                [
                    'ar' => 'الصناعات الغذائية',
                    'en' => 'Food Manufacturing',
                    'subs' => [
                        ['ar' => 'التمور ومنتجاتها', 'en' => 'Dates & Date Products'],
                        ['ar' => 'الألبان ومشتقاتها', 'en' => 'Dairy Products'],
                        ['ar' => 'منتجات وطنية', 'en' => 'Local Beverages'],
                        ['ar' => 'منتجات معلبة', 'en' => 'Canned Products'],
                        ['ar' => 'حلويات ومعجنات', 'en' => 'Sweets & Pastries'],
                    ],
                ],

                // ===============================
                // 5️⃣ Electronics & Tech
                // ===============================
                [
                    'ar' => 'الأجهزة والإلكترونيات',
                    'en' => 'Electronics & Technology',
                    'subs' => [
                        ['ar' => 'ملحقات إلكترونية', 'en' => 'Electronic Accessories'],
                        ['ar' => 'أنظمة أمن وكاميرات', 'en' => 'Security & Cameras'],
                        ['ar' => 'إنارة LED', 'en' => 'LED Lighting'],
                        ['ar' => 'سمارت هوم', 'en' => 'Smart Home Devices'],
                        ['ar' => 'قطع غيار سيارات', 'en' => 'Auto Spare Parts'],
                    ],
                ],

                // ===============================
                // 6️⃣ B2B Products
                // ===============================
                [
                    'ar' => 'منتجات الشركات',
                    'en' => 'B2B Products',
                    'subs' => [
                        ['ar' => 'مطابخ صناعية', 'en' => 'Industrial Kitchens'],
                        ['ar' => 'أثاث مكتبي', 'en' => 'Office Furniture'],
                        ['ar' => 'زي موحد', 'en' => 'Uniforms'],
                        ['ar' => 'مواد تغليف', 'en' => 'Packaging Materials'],
                        ['ar' => 'مواد نظافة', 'en' => 'Cleaning Products'],
                    ],
                ],

                // ===============================
                // 7️⃣ Agricultural Products
                // ===============================
                [
                    'ar' => 'المنتجات الزراعية السعودية',
                    'en' => 'Saudi Agricultural Products',
                    'subs' => [
                        ['ar' => 'خضروات وفواكه', 'en' => 'Vegetables & Fruits'],
                        ['ar' => 'منتجات المزارع', 'en' => 'Farm Supplies'],
                        ['ar' => 'عسل سعودي', 'en' => 'Saudi Honey'],
                    ],
                ],

                // ===============================
                // 8️⃣ Creative Local Goods
                // ===============================
                [
                    'ar' => 'منتجات إبداعية محلية',
                    'en' => 'Creative Local Goods',
                    'subs' => [
                        ['ar' => 'ديكور فني', 'en' => 'Art Decor'],
                        ['ar' => 'طباعة حسب الطلب', 'en' => 'Print on Demand'],
                        ['ar' => 'تصميمات محلية', 'en' => 'Local Design Products'],
                    ],
                ],
            ];

            foreach ($categories as $category) {

                $main = Category::create([
                    'parent_id' => null,
                    'level' => 1,
                    'is_active' => true,
                ]);

                $this->translate($main, $category['ar'], $category['en']);

                // 🔸 Sub Categories
                foreach ($category['subs'] as $sub) {
                    $child = Category::create([
                        'parent_id' => $main->id,
                        'level' => 2,
                        'is_active' => true,
                    ]);

                    $this->translate($child, $sub['ar'], $sub['en']);
                }
            }
        });
    }

    private function translate(Category $category, string $ar, string $en): void
    {
        CategoryTranslation::insert([
            [
                'category_id' => $category->id,
                'locale' => 'ar',
                'name' => $ar,
            ],
            [
                'category_id' => $category->id,
                'locale' => 'en',
                'name' => $en,
            ],
        ]);
    }
}
