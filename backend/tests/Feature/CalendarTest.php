<?php

namespace Tests\Feature;

use App\Contracts\CalendarProvider;
use App\Jobs\SyncReminderToCalendar;
use App\Models\CalendarConnection;
use App\Models\Reminder;
use App\Services\Calendar\IcsGenerator;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesTenants;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_ics_generator_produces_valid_vcalendar(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        $reminder = Reminder::factory()->create([
            'organization_id' => $org->id,
            'title' => 'Car insurance',
            'expiry_date' => '2026-12-01',
        ]);

        $ics = app(IcsGenerator::class)->forReminders([$reminder]);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('BEGIN:VEVENT', $ics);
        $this->assertStringContainsString('SUMMARY:Car insurance', $ics);
        $this->assertStringContainsString('DTSTART;VALUE=DATE:20261201', $ics);
        $this->assertStringContainsString('BEGIN:VALARM', $ics);
        $this->assertStringContainsString("\r\n", $ics);
    }

    public function test_a_user_can_download_a_reminder_as_ics(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        $reminder = Reminder::factory()->create(['organization_id' => $org->id]);

        $res = $this->actingAs($user)->get("/api/calendar/reminders/{$reminder->id}.ics");
        $res->assertOk();
        $res->assertHeader('content-type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $res->getContent());
    }

    public function test_connections_report_sync_as_unavailable_without_a_provider(): void
    {
        [$user] = $this->createUserWithOrganization();

        $this->actingAs($user)->getJson('/api/calendar/connections')
            ->assertOk()
            ->assertJsonPath('data.sync_available', false);
    }

    public function test_connect_is_blocked_without_credentials(): void
    {
        [$user] = $this->createUserWithOrganization();

        $this->actingAs($user)->postJson('/api/calendar/connect/google')->assertStatus(422);
    }

    public function test_sync_job_records_skipped_when_provider_not_configured(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        Tenancy::set($org->id);
        $reminder = Reminder::factory()->create(['organization_id' => $org->id]);

        $connection = CalendarConnection::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'provider' => 'google',
            'sync_enabled' => true,
        ]);

        // Run the job synchronously with the (default) null provider.
        (new SyncReminderToCalendar($reminder->id, $connection->id, 'create'))
            ->handle(app(CalendarProvider::class));

        $this->assertDatabaseHas('calendar_sync_logs', [
            'reminder_id' => $reminder->id,
            'status' => 'skipped',
            'action' => 'provider_not_configured',
        ]);
    }
}
