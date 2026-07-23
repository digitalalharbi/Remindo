<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\FeatureFlag;
use App\Models\Invoice;
use App\Models\Language;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesTenants;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->seed(PlatformSeeder::class);
    }

    private function admin(): User
    {
        [$admin] = $this->createUserWithOrganization(['is_super_admin' => true]);

        return $admin;
    }

    public function test_admin_can_create_and_toggle_a_plan(): void
    {
        $admin = $this->admin();

        $res = $this->actingAs($admin)->postJson('/api/admin/plans', [
            'key' => 'startup',
            'name' => ['en' => 'Startup', 'ar' => 'ناشئ'],
            'price_monthly' => 2900,
            'price_yearly' => 29000,
            'currency' => 'SAR',
            'reminder_limit' => 500,
            'user_limit' => 5,
            'ai_operations_limit' => 200,
            'features' => ['email', 'sharing'],
            'is_active' => true,
            'prices' => [
                ['currency' => 'USD', 'price_monthly' => 799, 'price_yearly' => 7990],
            ],
        ]);
        $res->assertCreated();

        $plan = Plan::where('key', 'startup')->with('prices')->first();
        $this->assertNotNull($plan);
        $this->assertEquals(799, $plan->monthlyPriceFor('USD'));
        $this->assertEquals(2900, $plan->monthlyPriceFor('SAR')); // base fallback

        // Toggle off
        $this->actingAs($admin)->postJson("/api/admin/plans/{$plan->id}/toggle")->assertOk();
        $this->assertFalse($plan->fresh()->is_active);

        // Audit trail recorded
        $this->assertDatabaseHas('activity_logs', ['action' => 'admin.plan.created']);
    }

    public function test_non_admin_cannot_create_plans(): void
    {
        [$user] = $this->createUserWithOrganization();
        $this->actingAs($user)->postJson('/api/admin/plans', ['key' => 'x'])->assertStatus(403);
    }

    public function test_admin_can_create_a_coupon_and_it_applies_at_checkout(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/api/admin/coupons', [
            'code' => 'launch50',
            'type' => 'percent',
            'value' => 50,
            'duration' => 'once',
        ])->assertCreated();

        $coupon = Coupon::where('code', 'LAUNCH50')->first();
        $this->assertNotNull($coupon);

        // A normal user subscribes with the coupon → invoice reflects 50% off.
        [$user, $org] = $this->createUserWithOrganization();
        $this->actingAs($user)->postJson('/api/subscription', [
            'plan_key' => 'professional',
            'interval' => 'monthly',
            'coupon_code' => 'launch50',
        ])->assertOk();

        $invoice = Invoice::where('organization_id', $org->id)->first();
        $this->assertEquals(950, $invoice->subtotal); // 1900 - 50%
        $this->assertEquals(1, $coupon->fresh()->times_redeemed);
    }

    public function test_admin_can_suspend_and_reactivate_a_user(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['password' => 'password123']);

        $this->actingAs($admin)->postJson("/api/admin/users/{$target->id}/suspend", [
            'reason' => 'abuse',
        ])->assertOk();
        $this->assertTrue($target->fresh()->isSuspended());

        // Suspended user cannot log in.
        $this->postJson('/api/auth/login', [
            'email' => $target->email,
            'password' => 'password123',
        ])->assertStatus(422);

        $this->actingAs($admin)->postJson("/api/admin/users/{$target->id}/reactivate")->assertOk();
        $this->assertFalse($target->fresh()->isSuspended());
    }

    public function test_admin_cannot_suspend_a_super_admin(): void
    {
        $admin = $this->admin();
        $other = User::factory()->create(['is_super_admin' => true]);

        $this->actingAs($admin)->postJson("/api/admin/users/{$other->id}/suspend")->assertStatus(403);
    }

    public function test_suspended_organization_blocks_operational_api(): void
    {
        $admin = $this->admin();
        [$user, $org] = $this->createUserWithOrganization();

        $this->actingAs($admin)->postJson("/api/admin/organizations/{$org->id}/suspend")->assertOk();

        $this->actingAs($user)->getJson('/api/reminders')->assertStatus(403);
    }

    public function test_admin_can_toggle_a_feature_flag(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/api/admin/flags', [
            'key' => 'oauth_google',
            'enabled' => true,
        ])->assertOk();

        $this->assertTrue(FeatureFlag::where('key', 'oauth_google')->value('enabled'));
    }

    public function test_admin_cannot_disable_the_default_language(): void
    {
        $admin = $this->admin();
        $en = Language::where('code', 'en')->first();

        $this->actingAs($admin)->postJson("/api/admin/languages/{$en->id}/toggle")->assertStatus(422);
    }
}
