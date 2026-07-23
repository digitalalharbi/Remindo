<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesTenants;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_non_admins_are_forbidden_from_the_admin_api(): void
    {
        [$user] = $this->createUserWithOrganization();

        $this->actingAs($user)->getJson('/api/admin/stats')->assertStatus(403);
    }

    public function test_a_super_admin_can_read_platform_stats(): void
    {
        [$admin] = $this->createUserWithOrganization(['is_super_admin' => true]);

        $this->actingAs($admin)->getJson('/api/admin/stats')
            ->assertOk()
            ->assertJsonStructure(['data' => ['users', 'organizations', 'reminders', 'revenue_total']]);
    }

    public function test_a_super_admin_can_list_users_across_the_platform(): void
    {
        User::factory()->count(3)->create();
        [$admin] = $this->createUserWithOrganization(['is_super_admin' => true]);

        $this->actingAs($admin)->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'total']]);
    }
}
