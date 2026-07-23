<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Models\ReminderNotification;
use App\Notifications\ReminderDueNotification;
use App\Services\ReminderService;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\CreatesTenants;
use Tests\TestCase;

class ReminderSchedulingTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_due_notifications_are_dispatched_and_marked_sent(): void
    {
        Notification::fake();

        [$user, $org] = $this->createUserWithOrganization();

        $reminder = Reminder::factory()->create([
            'organization_id' => $org->id,
            'created_by' => $user->id,
            'expiry_date' => now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        // A notification that is already due.
        $notification = ReminderNotification::create([
            'reminder_id' => $reminder->id,
            'offset_days' => 7,
            'channel' => 'email',
            'send_at' => now()->subMinute(),
            'status' => 'pending',
        ]);

        $this->artisan('reminders:dispatch-due')->assertSuccessful();

        $this->assertEquals('sent', $notification->fresh()->status);
        Notification::assertSentTo($user, ReminderDueNotification::class);
    }

    public function test_future_notifications_are_not_dispatched(): void
    {
        Notification::fake();

        [$user, $org] = $this->createUserWithOrganization();
        $reminder = Reminder::factory()->create(['organization_id' => $org->id, 'created_by' => $user->id]);

        ReminderNotification::create([
            'reminder_id' => $reminder->id,
            'offset_days' => 7,
            'channel' => 'email',
            'send_at' => now()->addDay(),
            'status' => 'pending',
        ]);

        $this->artisan('reminders:dispatch-due')->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertEquals(1, ReminderNotification::where('status', 'pending')->count());
    }

    public function test_completing_a_reminder_cancels_its_pending_notifications(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        Tenancy::set($org->id);

        $service = app(ReminderService::class);
        $reminder = $service->create([
            'created_by' => $user->id,
            'title' => 'Test',
            'expiry_date' => now()->addDays(30)->toDateString(),
            'reminder_offsets' => [7],
            'channels' => ['email'],
        ]);

        $this->assertEquals(1, $reminder->notifications()->where('status', 'pending')->count());

        $service->complete($reminder);

        $this->assertEquals(0, $reminder->notifications()->where('status', 'pending')->count());
        $this->assertEquals(1, $reminder->notifications()->where('status', 'cancelled')->count());
    }
}
