<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Models\ReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesTenants;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_a_user_can_create_a_reminder_with_a_notification_schedule(): void
    {
        [$user] = $this->createUserWithOrganization();

        $response = $this->actingAs($user)->postJson('/api/reminders', [
            'title' => 'Car insurance',
            'expiry_date' => now()->addDays(30)->toDateString(),
            'reminder_offsets' => [14, 7, 1],
            'channels' => ['email', 'in_app'],
        ]);

        $response->assertCreated()->assertJsonPath('data.title', 'Car insurance');

        $reminder = Reminder::first();
        $this->assertEquals($user->id, $reminder->created_by);
        // 3 offsets × 2 channels = 6 scheduled notifications.
        $this->assertEquals(6, $reminder->notifications()->count());
    }

    public function test_past_notification_times_are_not_scheduled_as_pending(): void
    {
        [$user] = $this->createUserWithOrganization();

        $this->actingAs($user)->postJson('/api/reminders', [
            'title' => 'Expires soon',
            'expiry_date' => now()->addDays(2)->toDateString(),
            'reminder_offsets' => [30, 1], // 30 days before is already in the past
            'channels' => ['email'],
        ])->assertCreated();

        $this->assertEquals(1, ReminderNotification::where('status', 'pending')->count());
        $this->assertEquals(1, ReminderNotification::where('status', 'cancelled')->count());
    }

    public function test_a_reminder_can_be_completed(): void
    {
        [$user] = $this->createUserWithOrganization();
        $reminder = Reminder::factory()->create(['organization_id' => $user->current_organization_id]);

        $this->actingAs($user)->postJson("/api/reminders/{$reminder->id}/complete")
            ->assertOk()->assertJsonPath('data.status', 'completed');

        $this->assertEquals('completed', $reminder->fresh()->status);
    }

    public function test_a_reminder_can_be_renewed_to_a_new_date(): void
    {
        [$user] = $this->createUserWithOrganization();
        $oldDate = now()->addDays(5)->toDateString();
        $reminder = Reminder::factory()->create([
            'organization_id' => $user->current_organization_id,
            'expiry_date' => $oldDate,
        ]);

        $newDate = now()->addYear()->toDateString();

        $this->actingAs($user)->postJson("/api/reminders/{$reminder->id}/renew", [
            'new_expiry_date' => $newDate,
        ])->assertOk()->assertJsonPath('data.expiry_date', $newDate);

        $fresh = $reminder->fresh();
        $this->assertEquals('active', $fresh->status);
        $this->assertEquals($oldDate, $fresh->issue_date->toDateString());
    }

    public function test_listing_supports_search_and_filters(): void
    {
        [$user] = $this->createUserWithOrganization();
        Reminder::factory()->create(['organization_id' => $user->current_organization_id, 'title' => 'Passport renewal']);
        Reminder::factory()->create(['organization_id' => $user->current_organization_id, 'title' => 'Domain hosting']);

        $this->actingAs($user)->getJson('/api/reminders?search=passport')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Passport renewal');
    }

    public function test_the_free_plan_reminder_limit_is_enforced(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        // Free plan allows 5 active reminders.
        Reminder::factory()->count(5)->create(['organization_id' => $org->id, 'status' => 'active']);

        $this->actingAs($user)->postJson('/api/reminders', [
            'title' => 'One too many',
            'expiry_date' => now()->addDays(10)->toDateString(),
        ])->assertStatus(402);
    }
}
