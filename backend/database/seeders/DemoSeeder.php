<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Reminder;
use App\Models\User;
use App\Services\OrganizationService;
use App\Services\ReminderService;
use App\Support\Tenancy;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@remindo.me'],
            [
                'name' => 'Remindo Demo',
                'password' => 'password123',
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

            $samples = [
                ['title' => 'Car insurance', 'category' => 'insurance', 'days' => 20, 'recurrence' => 'yearly'],
                ['title' => 'Commercial registration', 'category' => 'licenses', 'days' => 45, 'recurrence' => 'yearly'],
                ['title' => 'Office lease', 'category' => 'rentals', 'days' => 5, 'recurrence' => 'yearly'],
                ['title' => 'Domain renewal', 'category' => 'subscriptions', 'days' => 60, 'recurrence' => 'yearly'],
                ['title' => 'Passport', 'category' => 'documents', 'days' => -3, 'recurrence' => 'none'],
            ];

            foreach ($samples as $s) {
                $service->create([
                    'created_by' => $org->owner_id,
                    'title' => $s['title'],
                    'category_id' => $cat($s['category']),
                    'expiry_date' => now()->addDays($s['days'])->toDateString(),
                    'recurrence' => $s['recurrence'],
                    'reminder_offsets' => [30, 7, 1],
                    'channels' => ['email', 'in_app'],
                ]);
            }
        });
    }
}
