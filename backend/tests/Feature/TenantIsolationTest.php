<?php

namespace Tests\Feature;

use App\Models\Reminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesTenants;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_a_user_cannot_see_another_organizations_reminders(): void
    {
        [$alice, $aliceOrg] = $this->createUserWithOrganization(['email' => 'alice@example.com']);
        Reminder::factory()->count(3)->create(['organization_id' => $aliceOrg->id]);

        [$bob] = $this->createUserWithOrganization(['email' => 'bob@example.com']);

        // Bob is the active tenant now; he should see none of Alice's reminders.
        $this->actingAs($bob)->getJson('/api/reminders')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_a_user_cannot_access_another_organizations_reminder_by_id(): void
    {
        [, $aliceOrg] = $this->createUserWithOrganization(['email' => 'alice2@example.com']);
        $reminder = Reminder::factory()->create(['organization_id' => $aliceOrg->id]);

        [$bob] = $this->createUserWithOrganization(['email' => 'bob2@example.com']);

        // The global tenant scope hides the row entirely → 404, not 403.
        $this->actingAs($bob)->getJson("/api/reminders/{$reminder->id}")
            ->assertNotFound();
    }
}
