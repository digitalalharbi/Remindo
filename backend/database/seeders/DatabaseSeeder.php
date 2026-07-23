<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $plans = collect([
            ['code' => 'free', 'name' => 'Free', 'price_monthly' => 0, 'reminder_limit' => 5, 'email_limit' => 25],
            ['code' => 'personal', 'name' => 'Personal', 'price_monthly' => 900, 'reminder_limit' => 50, 'email_limit' => 500],
            ['code' => 'business', 'name' => 'Business', 'price_monthly' => 4900, 'reminder_limit' => 1000, 'email_limit' => 10000],
        ])->map(fn (array $plan) => Plan::updateOrCreate(['code' => $plan['code']], $plan + ['currency' => 'SAR', 'active' => true]));

        if (app()->environment('production')) {
            return;
        }

        $user = User::updateOrCreate(['email' => 'demo@remindo.test'], [
            'name' => 'Remindo Demo',
            'password' => Hash::make('DemoPass123'),
            'email_verified_at' => now(),
            'timezone' => 'Asia/Riyadh',
            'locale' => 'ar',
            'country' => 'SA',
            'currency' => 'SAR',
        ]);
        $user->subscriptions()->firstOrCreate(['status' => 'active'], ['plan_id' => $plans->firstWhere('code', 'personal')->id]);

        if ($user->reminders()->exists()) {
            return;
        }

        foreach ([
            ['Insurance renewal', now()->addDays(7), 'email'],
            ['Passport expiration', now()->addMonths(3), 'in_app'],
            ['Software subscription', now()->addMonth(), 'sms'],
        ] as [$name, $expiry, $channel]) {
            $reminder = Reminder::create([
                'user_id' => $user->id,
                'name' => $name,
                'expires_at' => $expiry,
                'status' => 'active',
                'priority' => 'normal',
                'recurrence' => 'yearly',
            ]);
            $reminder->schedules()->create([
                'channel' => $channel,
                'scheduled_at' => $expiry->copy()->subDays(3),
                'idempotency_key' => hash('sha256', $reminder->id.'|'.$channel),
            ]);
        }
    }
}
