<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Reminder;
use App\Models\User;
use App\Services\OrganizationService;
use App\Services\ReminderService;
use App\Support\Tenancy;
use Illuminate\Database\Seeder;

/**
 * Preview / demo data for the public preview environment.
 *
 * Never runs in production (gated in DatabaseSeeder). The account password is
 * env-driven (DEMO_SEED_PASSWORD) so preview and any other environment can set
 * their own; it defaults to the documented preview password.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@remindo.me'],
            [
                'name' => 'Remindo Demo',
                'password' => env('DEMO_SEED_PASSWORD', 'DemoPass123!'),
                'locale' => 'en',
                'country' => 'SA',
                'timezone' => 'Asia/Riyadh',
                'email_verified_at' => now(),
            ],
        );

        $org = $user->currentOrganization
            ?? app(OrganizationService::class)->createPersonalOrganizationFor($user);

        Tenancy::forOrganization($org->id, function () use ($org) {
            if (Reminder::count() > 0) {
                return;
            }

            $service = app(ReminderService::class);
            $cat = fn (string $slug) => Category::withoutGlobalScope('organization')->where('slug', $slug)->value('id');

            // A realistic mix a small business would track, spanning every
            // lifecycle state: overdue, due-soon, upcoming, and renewed.
            $samples = [
                [
                    'title' => 'Car insurance — Toyota Hilux',
                    'description' => 'تأمين المركبة · Comprehensive motor policy (renews yearly).',
                    'category' => 'insurance', 'issuer' => 'Tawuniya', 'reference_number' => 'POL-2025-88213',
                    'days' => 18, 'recurrence' => 'yearly', 'offsets' => [30, 7, 1],
                ],
                [
                    'title' => 'Supplier contract — Gulf Supplies Co.',
                    'description' => 'عقد المورد · Annual procurement agreement, auto-review before renewal.',
                    'category' => 'contracts', 'issuer' => 'Gulf Supplies Co.', 'reference_number' => 'AGR-4471',
                    'days' => 33, 'recurrence' => 'yearly', 'offsets' => [60, 14, 3],
                ],
                [
                    'title' => 'Commercial registration (CR)',
                    'description' => 'السجل التجاري · Ministry of Commerce commercial registration.',
                    'category' => 'licenses', 'issuer' => 'Ministry of Commerce', 'reference_number' => 'CR-1010574839',
                    'days' => 9, 'recurrence' => 'yearly', 'offsets' => [30, 7, 1],
                ],
                [
                    'title' => 'Software subscription — Design suite',
                    'description' => 'اشتراك البرمجيات · Team seats billed monthly.',
                    'category' => 'subscriptions', 'issuer' => 'Adobe', 'reference_number' => 'SUB-99120',
                    'days' => 60, 'recurrence' => 'monthly', 'offsets' => [7, 1],
                ],
                [
                    'title' => 'Professional license — Engineering',
                    'description' => 'رخصة مهنية · Saudi Council of Engineers membership.',
                    'category' => 'licenses', 'issuer' => 'Saudi Council of Engineers', 'reference_number' => 'SCE-556201',
                    'days' => 14, 'recurrence' => 'yearly', 'offsets' => [30, 7, 1],
                ],
                [
                    'title' => 'Employee certificate — First aid',
                    'description' => 'شهادة موظف · Workplace first-aid certification (overdue).',
                    'category' => 'certificates', 'issuer' => 'Saudi Red Crescent', 'reference_number' => 'FA-2024-771',
                    'days' => -5, 'recurrence' => 'yearly', 'offsets' => [30, 7, 1],
                ],
                [
                    'title' => 'Office lease',
                    'description' => 'عقد الإيجار · Ejar registered lease agreement.',
                    'category' => 'rentals', 'issuer' => 'Ejar', 'reference_number' => 'EJ-2025-0098',
                    'days' => 4, 'recurrence' => 'yearly', 'offsets' => [30, 7, 1],
                ],
                [
                    'title' => 'Domain renewal — remindo.me',
                    'description' => 'تجديد النطاق · Primary domain registration.',
                    'category' => 'subscriptions', 'issuer' => 'Namecheap', 'reference_number' => 'DN-88431',
                    'days' => 72, 'recurrence' => 'yearly', 'offsets' => [30, 7, 1],
                ],
                [
                    'title' => 'Passport',
                    'description' => 'جواز السفر · Travel document (overdue — renew now).',
                    'category' => 'documents', 'issuer' => 'Ministry of Interior', 'reference_number' => 'P-A1234567',
                    'days' => -2, 'recurrence' => 'none', 'offsets' => [30, 7, 1],
                ],
            ];

            foreach ($samples as $s) {
                $service->create([
                    'created_by' => $org->owner_id,
                    'title' => $s['title'],
                    'description' => $s['description'],
                    'issuer' => $s['issuer'],
                    'reference_number' => $s['reference_number'],
                    'category_id' => $cat($s['category']),
                    'expiry_date' => now()->addDays($s['days'])->toDateString(),
                    'recurrence' => $s['recurrence'],
                    'reminder_offsets' => $s['offsets'],
                    'channels' => ['email', 'in_app'],
                ]);
            }

            // A "renewed" record: vehicle registration renewed last month for a
            // fresh year — shows the renewed lifecycle state in the dashboard.
            $renewed = $service->create([
                'created_by' => $org->owner_id,
                'title' => 'Vehicle registration (Istimara)',
                'description' => 'استمارة المركبة · Renewed last month for another year.',
                'issuer' => 'Absher', 'reference_number' => 'REG-224417',
                'category_id' => $cat('licenses'),
                'expiry_date' => now()->subDays(30)->toDateString(),
                'recurrence' => 'yearly',
                'reminder_offsets' => [30, 7, 1],
                'channels' => ['email', 'in_app'],
            ]);
            $service->renew($renewed, now()->addDays(335)->toDateString());
            $renewed->update(['status' => 'renewed']);
        });
    }
}
