<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Subscribe / upgrade / downgrade / cancel an organization's plan. Charges via
 * the configured PaymentGateway (sandbox by default) and issues an invoice in
 * the organization's currency. Card data never touches the application.
 */
class BillingService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * Subscribe (or change) an organization to a plan on the given interval.
     * Free plans skip payment; paid plans are charged through the gateway.
     */
    public function subscribe(Organization $organization, Plan $plan, string $interval = 'monthly'): Subscription
    {
        $amount = $interval === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        return DB::transaction(function () use ($organization, $plan, $interval, $amount) {
            if ($amount > 0) {
                $charge = $this->gateway->charge($amount, $organization->currency, [
                    'organization_id' => $organization->id,
                    'plan' => $plan->key,
                    'interval' => $interval,
                ]);

                if (! $charge->success) {
                    throw new RuntimeException($charge->failureReason ?? 'payment_failed');
                }
                $reference = $charge->reference;
                $provider = $charge->provider;
            } else {
                $reference = null;
                $provider = $this->gateway->providerName();
            }

            $periodEnd = $interval === 'yearly' ? now()->addYear() : now()->addMonth();

            $subscription = Subscription::updateOrCreate(
                ['organization_id' => $organization->id, 'status' => 'active'],
                [
                    'plan_id' => $plan->id,
                    'interval' => $interval,
                    'status' => 'active',
                    'provider' => $provider,
                    'provider_reference' => $reference,
                    'current_period_start' => now(),
                    'current_period_end' => $periodEnd,
                    'canceled_at' => null,
                ],
            );

            // Point the organization at the new plan (limits update immediately).
            $organization->update(['plan_id' => $plan->id]);

            if ($amount > 0) {
                $this->issueInvoice($organization, $subscription, $plan, $amount, $reference, $provider);
            }

            $this->activity->log('subscription.changed', $subscription, [
                'plan' => $plan->key,
                'interval' => $interval,
            ]);

            return $subscription;
        });
    }

    public function cancel(Organization $organization): void
    {
        Subscription::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->update(['status' => 'canceled', 'canceled_at' => now()]);

        // Drop back to the free plan at period end (simplified: immediately here).
        $free = Plan::where('key', 'free')->first();
        if ($free) {
            $organization->update(['plan_id' => $free->id]);
        }

        $this->activity->log('subscription.canceled');
    }

    private function issueInvoice(
        Organization $organization,
        Subscription $subscription,
        Plan $plan,
        int $amount,
        ?string $reference,
        string $provider,
    ): Invoice {
        // VAT is configurable per market; default 15% (KSA) applied to SAR here.
        $taxRate = $organization->currency === 'SAR' ? 0.15 : 0.0;
        $tax = (int) round($amount * $taxRate);

        return Invoice::create([
            'organization_id' => $organization->id,
            'subscription_id' => $subscription->id,
            'number' => 'INV-'.now()->format('Ym').'-'.strtoupper(Str::random(6)),
            'status' => 'paid',
            'subtotal' => $amount,
            'tax' => $tax,
            'total' => $amount + $tax,
            'currency' => $organization->currency,
            'provider' => $provider,
            'provider_reference' => $reference,
            'lines' => [[
                'description' => $plan->key,
                'amount' => $amount,
            ]],
            'issued_at' => now(),
        ]);
    }
}
