<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Reminder;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReminderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['timezone' => 'Asia/Riyadh']);
        $plan = Plan::create(['code' => 'free', 'name' => 'Free', 'reminder_limit' => 5, 'email_limit' => 25]);
        $this->user->subscriptions()->create(['plan_id' => $plan->id, 'status' => 'active']);
        Sanctum::actingAs($this->user);
    }

    public function test_reminder_creation_persists_utc_schedule(): void
    {
        CarbonImmutable::setTestNow('2026-07-23 09:00:00 UTC');
        $response = $this->postJson('/api/v1/reminders', [
            'name' => 'Car insurance',
            'expires_at' => '2026-08-23',
            'category' => 'insurance',
            'priority' => 'high',
            'recurrence' => 'yearly',
            'schedules' => [
                ['channel' => 'email', 'scheduled_at' => '2026-08-20 10:00:00'],
                ['channel' => 'in_app', 'scheduled_at' => '2026-08-22 10:00:00'],
            ],
        ])->assertCreated()->assertJsonCount(2, 'data.schedules');

        $this->assertDatabaseHas('reminder_schedules', [
            'reminder_id' => $response->json('data.id'),
            'channel' => 'email',
            'scheduled_at' => '2026-08-20 07:00:00',
        ]);
    }

    public function test_user_cannot_access_another_users_reminder(): void
    {
        $other = User::factory()->create();
        $reminder = Reminder::create([
            'user_id' => $other->id, 'name' => 'Private passport',
            'expires_at' => now()->addMonth(), 'status' => 'active',
        ]);
        $this->getJson("/api/v1/reminders/{$reminder->id}")->assertForbidden();
        $this->deleteJson("/api/v1/reminders/{$reminder->id}")->assertForbidden();
    }

    public function test_reminder_can_be_snoozed_completed_and_renewed(): void
    {
        $reminder = Reminder::create([
            'user_id' => $this->user->id, 'name' => 'License',
            'expires_at' => now()->addMonth(), 'status' => 'active',
        ]);
        $reminder->schedules()->create([
            'channel' => 'in_app', 'scheduled_at' => now()->addDay(),
            'idempotency_key' => 'lifecycle-schedule',
        ]);

        $this->postJson("/api/v1/reminders/{$reminder->id}/snooze", ['until' => now()->addDays(2)->toISOString()])
            ->assertOk()->assertJsonPath('data.status', 'snoozed');

        $this->postJson("/api/v1/reminders/{$reminder->id}/renew", ['expires_at' => now()->addYear()->toDateString()])
            ->assertCreated()->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('reminders', ['id' => $reminder->id, 'status' => 'renewed']);
        $this->assertDatabaseCount('reminders', 2);
    }

    public function test_plan_limit_is_enforced(): void
    {
        $this->user->subscriptions()->first()->plan->update(['reminder_limit' => 1]);
        Reminder::create(['user_id' => $this->user->id, 'name' => 'Existing', 'expires_at' => now()->addMonth(), 'status' => 'active']);

        $this->postJson('/api/v1/reminders', [
            'name' => 'Too many',
            'expires_at' => now()->addMonths(2)->toDateString(),
            'schedules' => [['channel' => 'email', 'scheduled_at' => now()->addMonth()->toISOString()]],
        ])->assertUnprocessable()->assertJsonValidationErrors('plan');
    }
}
