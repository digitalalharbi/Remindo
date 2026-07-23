<?php

namespace Database\Seeders;

use App\Models\CreditPack;
use App\Models\Faq;
use App\Models\FeatureFlag;
use App\Models\Language;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'english_name' => 'English', 'rtl' => false, 'is_default' => true, 'sort_order' => 0],
            ['code' => 'ar', 'name' => 'العربية', 'english_name' => 'Arabic', 'rtl' => true, 'is_default' => false, 'sort_order' => 1],
            ['code' => 'es', 'name' => 'Español', 'english_name' => 'Spanish', 'rtl' => false, 'is_default' => false, 'sort_order' => 2],
            ['code' => 'tr', 'name' => 'Türkçe', 'english_name' => 'Turkish', 'rtl' => false, 'is_default' => false, 'sort_order' => 3],
        ];
        foreach ($languages as $lang) {
            Language::updateOrCreate(['code' => $lang['code']], $lang + ['enabled' => true]);
        }

        $flags = [
            ['key' => 'oauth_google', 'description' => 'Enable Google sign-in', 'enabled' => false],
            ['key' => 'oauth_microsoft', 'description' => 'Enable Microsoft sign-in', 'enabled' => false],
            ['key' => 'calendar_sync', 'description' => 'Enable calendar integrations', 'enabled' => true],
            ['key' => 'web_push', 'description' => 'Enable web push notifications', 'enabled' => true],
            ['key' => 'sms_channel', 'description' => 'Enable SMS delivery (requires provider)', 'enabled' => false],
            ['key' => 'whatsapp_channel', 'description' => 'Enable WhatsApp delivery (requires provider)', 'enabled' => false],
            ['key' => 'ai_extraction', 'description' => 'Enable AI document extraction', 'enabled' => true],
        ];
        foreach ($flags as $flag) {
            FeatureFlag::updateOrCreate(['key' => $flag['key']], $flag);
        }

        $faqs = [
            [
                'category' => 'general',
                'question' => [
                    'en' => 'What is Remindo?',
                    'ar' => 'ما هو Remindo؟',
                    'es' => '¿Qué es Remindo?',
                    'tr' => 'Remindo nedir?',
                ],
                'answer' => [
                    'en' => 'Remindo reminds you before contracts, licenses, insurance, subscriptions, and documents expire — all in one simple place.',
                    'ar' => 'يذكّرك Remindo قبل انتهاء العقود والتراخيص والتأمينات والاشتراكات والوثائق — كلها في مكان واحد بسيط.',
                    'es' => 'Remindo te avisa antes de que venzan contratos, licencias, seguros, suscripciones y documentos, todo en un solo lugar.',
                    'tr' => 'Remindo; sözleşmeler, lisanslar, sigortalar, abonelikler ve belgeler sona ermeden önce sizi tek bir yerden uyarır.',
                ],
            ],
            [
                'category' => 'general',
                'question' => [
                    'en' => 'Is there a free plan?',
                    'ar' => 'هل توجد خطة مجانية؟',
                    'es' => '¿Hay un plan gratuito?',
                    'tr' => 'Ücretsiz plan var mı?',
                ],
                'answer' => [
                    'en' => 'Yes. The free plan includes 5 active reminders with email and in-app notifications — no credit card needed.',
                    'ar' => 'نعم. الخطة المجانية تشمل ٥ تذكيرات نشطة مع إشعارات البريد وداخل النظام — بدون بطاقة.',
                    'es' => 'Sí. El plan gratuito incluye 5 recordatorios activos con notificaciones por correo y en la app, sin tarjeta.',
                    'tr' => 'Evet. Ücretsiz plan, e-posta ve uygulama içi bildirimlerle 5 aktif hatırlatıcı içerir; kart gerekmez.',
                ],
            ],
            [
                'category' => 'privacy',
                'question' => [
                    'en' => 'Do you use my documents to train AI?',
                    'ar' => 'هل تستخدمون مستنداتي لتدريب الذكاء الاصطناعي؟',
                    'es' => '¿Usan mis documentos para entrenar la IA?',
                    'tr' => 'Belgelerimi yapay zekayı eğitmek için mi kullanıyorsunuz?',
                ],
                'answer' => [
                    'en' => 'No. Your documents are never used to train models, and extraction only runs when you explicitly request it.',
                    'ar' => 'لا. لا تُستخدم مستنداتك لتدريب النماذج، والاستخراج يعمل فقط عندما تطلبه صراحةً.',
                    'es' => 'No. Tus documentos nunca se usan para entrenar modelos y la extracción solo se ejecuta cuando lo solicitas.',
                    'tr' => 'Hayır. Belgeleriniz modelleri eğitmek için kullanılmaz ve çıkarım yalnızca siz istediğinizde çalışır.',
                ],
            ],
        ];
        foreach ($faqs as $i => $faq) {
            Faq::updateOrCreate(
                ['category' => $faq['category'], 'sort_order' => $i],
                $faq + ['published' => true, 'sort_order' => $i],
            );
        }

        // Purchasable credit packs (editable from the Super Admin panel).
        $packs = [
            ['channel' => 'sms', 'name' => '100 SMS', 'credits' => 100, 'price' => 2500],
            ['channel' => 'sms', 'name' => '500 SMS', 'credits' => 500, 'price' => 10000],
            ['channel' => 'whatsapp', 'name' => '100 WhatsApp', 'credits' => 100, 'price' => 3000],
            ['channel' => 'ai', 'name' => '200 AI operations', 'credits' => 200, 'price' => 2000],
        ];
        foreach ($packs as $pack) {
            CreditPack::updateOrCreate(
                ['channel' => $pack['channel'], 'name' => $pack['name']],
                $pack + ['currency' => 'SAR', 'is_active' => true],
            );
        }
    }
}
