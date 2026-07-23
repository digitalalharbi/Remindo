<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SubscriptionLimitService
{
    public function assertCanCreateReminder(User $user): void
    {
        $limit = $user->subscriptions()
            ->where('status', 'active')
            ->with('plan')
            ->latest()
            ->first()?->plan?->reminder_limit
            ?? Plan::query()->where('code', 'free')->value('reminder_limit')
            ?? 5;

        if ($user->reminders()->whereNull('deleted_at')->whereNotIn('status', ['archived', 'completed', 'cancelled'])->count() >= $limit) {
            throw ValidationException::withMessages([
                'plan' => ['Your active reminder limit has been reached.'],
            ]);
        }
    }
}
