<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\CreatesTenants;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_a_user_can_enable_confirm_and_use_two_factor(): void
    {
        [$user] = $this->createUserWithOrganization();
        $g2fa = new Google2FA;

        // Enable → get a secret
        $res = $this->actingAs($user)->postJson('/api/auth/two-factor/enable');
        $res->assertOk();
        $secret = $res->json('data.secret');
        $this->assertNotEmpty($secret);
        $this->assertNotEmpty($res->json('data.qr_svg'));

        // Confirm with a valid code → recovery codes issued
        $code = $g2fa->getCurrentOtp($secret);
        $confirm = $this->actingAs($user)->postJson('/api/auth/two-factor/confirm', ['code' => $code]);
        $confirm->assertOk();
        $this->assertCount(8, $confirm->json('data.recovery_codes'));
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_confirm_rejects_an_invalid_code(): void
    {
        [$user] = $this->createUserWithOrganization();
        $this->actingAs($user)->postJson('/api/auth/two-factor/enable');

        $this->actingAs($user)->postJson('/api/auth/two-factor/confirm', ['code' => '000000'])
            ->assertStatus(422);
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_login_requires_a_second_factor_when_enabled(): void
    {
        $g2fa = new Google2FA;
        $secret = $g2fa->generateSecretKey();

        [$user] = $this->createUserWithOrganization(['password' => 'password123']);
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['AAAA-BBBB'],
        ])->save();

        // Password login returns a 2FA challenge instead of a full session.
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertOk()
            ->assertJsonPath('data.two_factor', true);

        // Not authenticated yet.
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }

    public function test_two_factor_can_be_disabled(): void
    {
        [$user] = $this->createUserWithOrganization();
        $user->forceFill(['two_factor_secret' => 'x', 'two_factor_confirmed_at' => now()])->save();

        $this->actingAs($user)->deleteJson('/api/auth/two-factor')->assertOk();
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }
}
