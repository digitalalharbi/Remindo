<?php

namespace App\Jobs;

use App\Models\ReminderNotification;
use App\Models\ReminderSchedule;
use App\Services\NotificationChannelManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

class SendReminderNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public readonly string $scheduleId) {}

    public function handle(NotificationChannelManager $channels): void
    {
        $schedule = ReminderSchedule::query()->with('reminder.user')->findOrFail($this->scheduleId);
        if ($schedule->sent_at || $schedule->status === 'sent') {
            return;
        }

        try {
            $result = $channels->send($schedule);

            DB::transaction(function () use ($schedule, $result): void {
                $attempt = $schedule->attempts + 1;
                $schedule->update(['attempts' => $attempt, 'status' => 'sent', 'sent_at' => now(), 'last_error' => null]);
                DB::table('notification_attempts')->insert([
                    'reminder_schedule_id' => $schedule->id,
                    'attempt_number' => $attempt,
                    'provider' => $result['provider'],
                    'status' => 'sent',
                    'response' => null,
                    'attempted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                ReminderNotification::firstOrCreate(
                    ['reminder_schedule_id' => $schedule->id],
                    [
                        'user_id' => $schedule->reminder->user_id,
                        'reminder_id' => $schedule->reminder_id,
                        'channel' => $schedule->channel,
                        'subject' => $result['subject'],
                        'body' => $result['body'],
                        'sent_at' => now(),
                    ],
                );
            });
        } catch (Throwable $exception) {
            $attempt = $schedule->attempts + 1;
            $schedule->update([
                'attempts' => $attempt,
                'status' => $attempt >= $this->tries ? 'failed' : 'pending',
                'failed_at' => $attempt >= $this->tries ? now() : null,
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);
            DB::table('notification_attempts')->insert([
                'reminder_schedule_id' => $schedule->id,
                'attempt_number' => $attempt,
                'provider' => $schedule->channel,
                'status' => 'failed',
                'response' => mb_substr($exception->getMessage(), 0, 2000),
                'attempted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            throw $exception;
        }
    }
}
