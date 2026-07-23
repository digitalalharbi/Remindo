<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_login_read_profile_and_logout(): void
    {
        Notification::fake();

        $registration = $this->postJson('/api/v1/auth/register', [
            'name' => 'Remindo User',
            'email' => 'user@remindo.test',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'timezone' => 'Asia/Riyadh',
            'locale' => 'ar',
            'country' => 'SA',
        ])->assertCreated()->assertJsonStructure(['data' => ['user', 'token']]);

        $token = $registration->json('data.token');
        $this->withToken($token)->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'user@remindo.test')
            ->assertJsonPath('data.subscriptions.0.plan.code', 'free');

        Notification::assertSentTo(User::first(), VerifyEmail::class);

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@remindo.test',
            'password' => 'StrongPass123',
            'device_name' => 'test-suite',
        ])->assertOk()->assertJsonStructure(['data' => ['token']]);
    }

    public function test_disabled_user_cannot_login(): void
    {
        User::factory()->create(['email' => 'disabled@remindo.test', 'disabled_at' => now()]);
        $this->postJson('/api/v1/auth/login', [
            'email' => 'disabled@remindo.test',
            'password' => 'password',
        ])->assertUnprocessable();
    }

    public function test_private_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/reminders')->assertUnauthorized();
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }
}
