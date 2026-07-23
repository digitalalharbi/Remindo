<?php

namespace App\Services;

use App\Models\Reminder;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReminderService
{
    public function __construct(private readonly SubscriptionLimitService $limits) {}

    public function create(User $user, array $data): Reminder
    {
        $this->limits->assertCanCreateReminder($user);

        return DB::transaction(function () use ($user, $data): Reminder {
            $schedules = $data['schedules'];
            unset($data['schedules']);

            $reminder = $user->reminders()->create([...$data, 'status' => 'active']);

            foreach ($schedules as $schedule) {
                $scheduledAt = CarbonImmutable::parse($schedule['scheduled_at'], $user->timezone)->utc();
                $reminder->schedules()->create([
                    'channel' => $schedule['channel'],
                    'scheduled_at' => $scheduledAt,
                    'idempotency_key' => hash('sha256', $reminder->id.'|'.$schedule['channel'].'|'.$scheduledAt->toIso8601String()),
                ]);
            }

            return $reminder->load('schedules');
        });
    }

    public function snooze(Reminder $reminder, CarbonImmutable $until): Reminder
    {
        return DB::transaction(function () use ($reminder, $until): Reminder {
            $reminder->update(['status' => 'snoozed', 'snoozed_until' => $until->utc()]);
            $reminder->schedules()->where('status', 'pending')->update(['scheduled_at' => $until->utc()]);

            return $reminder->fresh('schedules');
        });
    }

    public function renew(Reminder $reminder, string $newExpiryDate): Reminder
    {
        return DB::transaction(function () use ($reminder, $newExpiryDate): Reminder {
            $reminder->update(['status' => 'renewed', 'renewed_at' => now()]);
            $next = $reminder->replicate(['id', 'status', 'renewed_at', 'completed_at']);
            $next->fill(['id' => (string) Str::uuid(), 'expires_at' => $newExpiryDate, 'status' => 'active']);
            $next->save();

            return $next;
        });
    }
}
