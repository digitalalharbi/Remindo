<?php

namespace Tests\Feature;

use App\Jobs\DispatchDueReminderSchedules;
use App\Jobs\SendReminderNotification;
use App\Mail\ReminderDueMail;
use App\Models\Reminder;
use App\Models\User;
use App\Services\NotificationChannelManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class NotificationEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_schedule_is_claimed_and_queued_once(): void
    {
        Queue::fake();
        $schedule = $this->dueSchedule('in_app');

        (new DispatchDueReminderSchedules)->handle();
        (new DispatchDueReminderSchedules)->handle();

        Queue::assertPushed(SendReminderNotification::class, 1);
        $this->assertDatabaseHas('reminder_schedules', ['id' => $schedule->id, 'status' => 'queued']);
    }

    public function test_in_app_notification_is_sent_logged_and_idempotent(): void
    {
        $schedule = $this->dueSchedule('in_app');
        $job = new SendReminderNotification($schedule->id);
        $job->handle(app(NotificationChannelManager::class));
        $job->handle(app(NotificationChannelManager::class));

        $this->assertDatabaseHas('reminder_schedules', ['id' => $schedule->id, 'status' => 'sent', 'attempts' => 1]);
        $this->assertDatabaseCount('reminder_notifications', 1);
        $this->assertDatabaseCount('notification_attempts', 1);
    }

    public function test_email_channel_uses_laravel_mail_and_logs_success(): void
    {
        Mail::fake();
        $schedule = $this->dueSchedule('email');
        (new SendReminderNotification($schedule->id))->handle(app(NotificationChannelManager::class));

        Mail::assertSent(ReminderDueMail::class, 1);
        $this->assertDatabaseHas('notification_attempts', [
            'reminder_schedule_id' => $schedule->id,
            'status' => 'sent',
        ]);
    }

    public function test_failed_delivery_is_recorded_for_retry(): void
    {
        $schedule = $this->dueSchedule('email');
        $channels = \Mockery::mock(NotificationChannelManager::class);
        $channels->shouldReceive('send')->once()->andThrow(new RuntimeException('Provider unavailable'));

        try {
            (new SendReminderNotification($schedule->id))->handle($channels);
            $this->fail('The job should rethrow so the queue retries it.');
        } catch (RuntimeException) {
            $this->assertDatabaseHas('reminder_schedules', [
                'id' => $schedule->id, 'status' => 'pending', 'attempts' => 1,
            ]);
            $this->assertDatabaseHas('notification_attempts', [
                'reminder_schedule_id' => $schedule->id, 'status' => 'failed',
            ]);
        }
    }

    private function dueSchedule(string $channel)
    {
        $user = User::factory()->create(['locale' => 'en']);
        $reminder = Reminder::create([
            'user_id' => $user->id, 'name' => 'Passport',
            'expires_at' => now()->addWeek(), 'status' => 'active',
        ]);

        return $reminder->schedules()->create([
            'channel' => $channel,
            'scheduled_at' => now()->subMinute(),
            'idempotency_key' => "test-{$channel}-".uniqid(),
        ]);
    }
}
