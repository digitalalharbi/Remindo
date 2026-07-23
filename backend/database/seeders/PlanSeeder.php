<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Launch plans and prices. All values are editable from the Super Admin
     * panel; these are safe, documented defaults. Prices are in minor units
     * of the base currency (SAR): 900 = 9.00 SAR.
     */
    public function run(): void
    {
        $plans = [
            [
                'key' => 'free',
                'name' => ['en' => 'Free', 'ar' => 'مجاني', 'es' => 'Gratis', 'tr' => 'Ücretsiz'],
                'price_monthly' => 0, 'price_yearly' => 0,
                'reminder_limit' => 5, 'user_limit' => 1, 'ai_operations_limit' => 0,
                'features' => ['email', 'in_app'],
                'sort_order' => 0,
            ],
            [
                'key' => 'personal',
                'name' => ['en' => 'Personal', 'ar' => 'شخصي', 'es' => 'Personal', 'tr' => 'Kişisel'],
                'price_monthly' => 900, 'price_yearly' => 9000,
                'reminder_limit' => 50, 'user_limit' => 1, 'ai_operations_limit' => 20,
                'features' => ['email', 'in_app', 'web_push', 'documents', 'calendar_sync', 'recurring', 'ai_limited'],
                'sort_order' => 1,
            ],
            [
                'key' => 'professional',
                'name' => ['en' => 'Professional', 'ar' => 'احترافي', 'es' => 'Profesional', 'tr' => 'Profesyonel'],
                'price_monthly' => 1900, 'price_yearly' => 19000,
                'reminder_limit' => 250, 'user_limit' => 3, 'ai_operations_limit' => 100,
                'features' => ['email', 'in_app', 'web_push', 'documents', 'calendar_sync', 'recurring', 'sharing', 'import', 'reports_basic', 'ai_more'],
                'sort_order' => 2,
            ],
            [
                'key' => 'business',
                'name' => ['en' => 'Business', 'ar' => 'أعمال', 'es' => 'Empresa', 'tr' => 'İşletme'],
                'price_monthly' => 4900, 'price_yearly' => 49000,
                'reminder_limit' => 1000, 'user_limit' => 10, 'ai_operations_limit' => 500,
                'features' => ['email', 'in_app', 'web_push', 'documents', 'calendar_sync', 'recurring', 'sharing', 'import', 'reports', 'team_roles', 'activity_log', 'api', 'webhooks'],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['key' => $plan['key']], array_merge($plan, ['currency' => 'SAR', 'is_active' => true]));
        }
    }
}
