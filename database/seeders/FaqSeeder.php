<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'ordering' => 1,
                'ar' => [
                    'question' => 'كيف يمكنني إنشاء حساب جديد؟',
                    'answer'   => 'يمكنك إنشاء حساب جديد من خلال الضغط على زر تسجيل وإنشاء حساب باستخدام بريدك الإلكتروني أو رقم الهاتف.',
                ],
                'en' => [
                    'question' => 'How can I create a new account?',
                    'answer'   => 'You can create a new account by clicking on Sign Up and registering using your email address or phone number.',
                ],
            ],
            [
                'ordering' => 2,
                'ar' => [
                    'question' => 'ما هي طرق الدفع المتاحة؟',
                    'answer'   => 'نقبل عدة طرق للدفع مثل البطاقات البنكية، المحافظ الإلكترونية، والدفع عند الاستلام حسب منطقتك.',
                ],
                'en' => [
                    'question' => 'What payment methods are available?',
                    'answer'   => 'We accept multiple payment methods such as credit/debit cards, digital wallets, and cash on delivery depending on your location.',
                ],
            ],
            [
                'ordering' => 3,
                'ar' => [
                    'question' => 'كم يستغرق توصيل الطلب؟',
                    'answer'   => 'يتم توصيل الطلب عادة خلال 1 إلى 5 أيام عمل حسب موقعك ونوع الشحن.',
                ],
                'en' => [
                    'question' => 'How long does delivery take?',
                    'answer'   => 'Orders are usually delivered within 1 to 5 business days depending on your location and shipping method.',
                ],
            ],
            [
                'ordering' => 4,
                'ar' => [
                    'question' => 'هل يمكنني إرجاع أو استبدال المنتج؟',
                    'answer'   => 'نعم، يمكنك إرجاع أو استبدال المنتج خلال مدة محددة بشرط أن يكون في حالته الأصلية.',
                ],
                'en' => [
                    'question' => 'Can I return or exchange a product?',
                    'answer'   => 'Yes, you can return or exchange a product within a specific period provided it is in its original condition.',
                ],
            ],
            [
                'ordering' => 5,
                'ar' => [
                    'question' => 'كيف يمكنني تتبع طلبي؟',
                    'answer'   => 'يمكنك تتبع حالة طلبك من خلال صفحة الطلبات داخل حسابك باستخدام رقم الطلب.',
                ],
                'en' => [
                    'question' => 'How can I track my order?',
                    'answer'   => 'You can track your order status from the Orders page in your account using your order number.',
                ],
            ],
        ];

        foreach ($faqs as $data) {

            // 1️⃣ Create FAQ (default AR content)
            $faq = Faq::create([
                'ordering' => $data['ordering'],
                'is_active' => true,
            ]);

            // 2️⃣ Create translations (AR + EN)
            foreach (['ar', 'en'] as $locale) {
                FaqTranslation::create([
                    'question'  => $data[$locale]['question'],
                    'answer'    => $data[$locale]['answer'],
                    'faq_id' => $faq->id,
                    'locale' => $locale,
                ]);
            }
        }
    }
}
