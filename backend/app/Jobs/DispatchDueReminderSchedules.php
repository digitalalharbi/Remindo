<?php

namespace App\Jobs;

use App\Models\ReminderSchedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchDueReminderSchedules implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        ReminderSchedule::query()
            ->where(function ($query): void {
                $query->where('status', 'pending')
                    ->orWhere(function ($stale): void {
                        $stale->where('status', 'queued')->where('dispatched_at', '<=', now()->subMinutes(10));
                    });
            })
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit(500)
            ->pluck('id')
            ->each(function (string $id): void {
                $claimed = ReminderSchedule::query()
                    ->whereKey($id)
                    ->where(function ($query): void {
                        $query->where('status', 'pending')
                            ->orWhere(function ($stale): void {
                                $stale->where('status', 'queued')->where('dispatched_at', '<=', now()->subMinutes(10));
                            });
                    })
                    ->update(['status' => 'queued', 'dispatched_at' => now()]);

                if ($claimed === 1) {
                    SendReminderNotification::dispatch($id);
                }
            });
    }
}
