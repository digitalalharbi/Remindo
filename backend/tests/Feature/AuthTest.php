<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_a_user_can_register_and_gets_a_personal_organization(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'locale' => 'ar',
            'country' => 'SA',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'sara@example.com')
            ->assertJsonPath('data.locale', 'ar');

        $user = User::where('email', 'sara@example.com')->first();
        $this->assertNotNull($user->current_organization_id);
        $this->assertEquals(1, $user->organizations()->count());
        $this->assertEquals('SAR', $user->currentOrganization->currency);
        $this->assertEquals(Plan::where('key', 'free')->value('id'), $user->currentOrganization->plan_id);
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/auth/register', [
            'name' => 'Dup',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_a_user_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('data.email', $user->email);

        $this->postJson('/api/auth/logout')->assertOk();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }
}
