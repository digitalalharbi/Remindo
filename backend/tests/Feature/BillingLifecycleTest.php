<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payments\BillingService;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\CreatesTenants;
use Tests\TestCase;

class BillingLifecycleTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_cancellation_keeps_the_plan_until_period_end_then_downgrades(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        Tenancy::set($org->id);
        $billing = app(BillingService::class);

        $billing->subscribe($org, Plan::where('key', 'professional')->first(), 'monthly');
        $this->assertEquals('professional', $org->fresh()->plan->key);

        // Cancel → grace period; plan stays until current_period_end.
        $billing->cancel($org);
        $sub = Subscription::where('organization_id', $org->id)->first();
        $this->assertTrue($sub->cancel_at_period_end);
        $this->assertEquals('professional', $org->fresh()->plan->key);
        $this->assertTrue($sub->inGracePeriod());

        // Fast-forward past the period end → automatic downgrade.
        $sub->update(['current_period_end' => now()->subDay()]);
        $downgraded = $billing->downgradeExpired();
        $this->assertEquals(1, $downgraded);
        $this->assertEquals('free', $org->fresh()->plan->key);
    }

    public function test_a_trial_plan_starts_without_charging(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        Tenancy::set($org->id);

        $pro = Plan::where('key', 'professional')->first();
        $pro->update(['trial_days' => 14]);

        app(BillingService::class)->subscribe($org, $pro, 'monthly');

        $sub = Subscription::where('organization_id', $org->id)->first();
        $this->assertTrue($sub->onTrial());
        // No invoice during the trial.
        $this->assertEquals(0, Invoice::where('organization_id', $org->id)->count());
    }

    public function test_upgrading_prorates_the_unused_balance(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        Tenancy::set($org->id);
        $billing = app(BillingService::class);

        // On personal (1900/mo-ish personal is 900), mid-cycle.
        $billing->subscribe($org, Plan::where('key', 'personal')->first(), 'monthly');
        // Upgrade immediately to professional — proration credit should reduce the invoice.
        $billing->subscribe($org, Plan::where('key', 'professional')->first(), 'monthly');

        $invoices = Invoice::where('organization_id', $org->id)->orderBy('created_at')->get();
        // Second invoice (professional) is discounted by the personal proration credit.
        $this->assertLessThan(1900, $invoices->last()->subtotal);
    }

    public function test_dunning_downgrades_after_max_payment_failures(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        Tenancy::set($org->id);
        $billing = app(BillingService::class);
        $billing->subscribe($org, Plan::where('key', 'business')->first(), 'monthly');

        $sub = Subscription::where('organization_id', $org->id)->first();
        $billing->recordPaymentFailure($sub);
        $this->assertEquals('past_due', $sub->fresh()->status);

        $billing->recordPaymentFailure($sub->fresh());
        $billing->recordPaymentFailure($sub->fresh());
        $this->assertEquals('canceled', $sub->fresh()->status);
        $this->assertEquals('free', $org->fresh()->plan->key);
    }

    public function test_payment_webhook_is_idempotent(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        Tenancy::set($org->id);
        app(BillingService::class)->subscribe($org, Plan::where('key', 'business')->first(), 'monthly');
        $sub = Subscription::where('organization_id', $org->id)->first();
        $sub->update(['status' => 'past_due']);

        $payload = ['id' => 'evt_1', 'type' => 'payment.succeeded', 'data' => ['subscription_id' => $sub->id]];

        $this->postJson('/api/webhooks/payments/sandbox', $payload)->assertOk()->assertJsonPath('data.processed', true);
        $this->assertEquals('active', $sub->fresh()->status);

        // Replaying the same event id is a no-op.
        $this->postJson('/api/webhooks/payments/sandbox', $payload)->assertOk()->assertJsonPath('data.duplicate', true);
        $this->assertEquals(1, DB::table('processed_webhooks')->count());
    }

    public function test_admin_can_refund_an_invoice(): void
    {
        [$admin, $org] = $this->createUserWithOrganization(['is_super_admin' => true]);
        Tenancy::set($org->id);
        app(BillingService::class)->subscribe($org, Plan::where('key', 'professional')->first(), 'monthly');
        $invoice = Invoice::where('organization_id', $org->id)->first();

        $this->actingAs($admin)->postJson("/api/admin/invoices/{$invoice->id}/refund", ['reason' => 'goodwill'])
            ->assertOk();

        $this->assertEquals('refunded', $invoice->fresh()->status);
        $this->assertDatabaseHas('refunds', ['invoice_id' => $invoice->id]);
    }
}
