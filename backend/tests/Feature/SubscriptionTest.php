<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesTenants;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_subscribing_to_a_paid_plan_charges_and_issues_an_invoice(): void
    {
        [$user, $org] = $this->createUserWithOrganization();

        $res = $this->actingAs($user)->postJson('/api/subscription', [
            'plan_key' => 'professional',
            'interval' => 'monthly',
        ]);

        $res->assertOk()->assertJsonPath('data.key', 'professional');

        // Organization moved to the new plan (limits update immediately).
        $this->assertEquals('professional', $org->fresh()->plan->key);

        $subscription = Subscription::where('organization_id', $org->id)->first();
        $this->assertEquals('active', $subscription->status);
        $this->assertEquals('sandbox', $subscription->provider);

        // A paid invoice was issued in the org's currency (SAR + 15% VAT).
        $invoice = Invoice::where('organization_id', $org->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(1900, $invoice->subtotal);
        $this->assertEquals(285, $invoice->tax); // 15% of 1900
        $this->assertEquals(2185, $invoice->total);
    }

    public function test_yearly_interval_sets_a_one_year_period(): void
    {
        [$user, $org] = $this->createUserWithOrganization();

        $this->actingAs($user)->postJson('/api/subscription', [
            'plan_key' => 'personal',
            'interval' => 'yearly',
        ])->assertOk();

        $sub = Subscription::where('organization_id', $org->id)->first();
        $this->assertEquals('yearly', $sub->interval);
        $this->assertTrue($sub->current_period_end->greaterThan(now()->addMonths(11)));
    }

    public function test_free_plan_subscription_creates_no_invoice(): void
    {
        [$user, $org] = $this->createUserWithOrganization();

        $this->actingAs($user)->postJson('/api/subscription', [
            'plan_key' => 'free',
            'interval' => 'monthly',
        ])->assertOk();

        $this->assertEquals(0, Invoice::where('organization_id', $org->id)->count());
    }

    public function test_show_returns_current_plan_and_invoices(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        $this->actingAs($user)->postJson('/api/subscription', [
            'plan_key' => 'business',
            'interval' => 'monthly',
        ]);

        $this->actingAs($user)->getJson('/api/subscription')
            ->assertOk()
            ->assertJsonPath('data.plan.key', 'business')
            ->assertJsonPath('data.currency', 'SAR')
            ->assertJsonCount(1, 'data.invoices');
    }

    public function test_canceling_reverts_to_free_plan(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        $this->actingAs($user)->postJson('/api/subscription', [
            'plan_key' => 'personal',
            'interval' => 'monthly',
        ]);

        $this->actingAs($user)->deleteJson('/api/subscription')->assertOk();

        $this->assertEquals('free', $org->fresh()->plan->key);
    }
}
