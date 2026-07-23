<?php

namespace Tests\Feature;

use App\Models\FeatureFlag;
use App\Models\User;
use App\Services\OrganizationService;
use App\Support\Tenancy;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\CreatesTenants;
use Tests\TestCase;

class OAuthTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->seed(PlatformSeeder::class);
    }

    public function test_oauth_status_reports_disabled_without_credentials(): void
    {
        $this->getJson('/api/auth/oauth/status')
            ->assertOk()
            ->assertJsonPath('data.google', false)
            ->assertJsonPath('data.microsoft', false);
    }

    public function test_redirect_is_blocked_when_provider_is_not_configured(): void
    {
        $this->getJson('/api/auth/oauth/google/redirect')->assertStatus(422);
    }

    public function test_callback_links_provider_to_an_existing_email_account(): void
    {
        // Configure Google (id+secret + enabled flag) and stub Socialite.
        config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);
        FeatureFlag::updateOrCreate(['key' => 'oauth_google'], ['enabled' => true]);

        $existing = User::factory()->create(['email' => 'linked@example.com']);
        Tenancy::forOrganization(null, fn () => app(OrganizationService::class)->createPersonalOrganizationFor($existing));

        $abstractUser = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-123');
        $abstractUser->shouldReceive('getEmail')->andReturn('linked@example.com');
        $abstractUser->shouldReceive('getName')->andReturn('Linked User');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/api/auth/oauth/google/callback');

        // No duplicate account; the provider is linked to the existing user.
        $this->assertEquals(1, User::where('email', 'linked@example.com')->count());
        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'user_id' => $existing->id,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
