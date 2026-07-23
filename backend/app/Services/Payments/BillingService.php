<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Refund;
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
    public function subscribe(Organization $organization, Plan $plan, string $interval = 'monthly', ?Coupon $coupon = null): Subscription
    {
        $plan->loadMissing('prices');
        $base = $interval === 'yearly'
            ? $plan->yearlyPriceFor($organization->currency)
            : $plan->monthlyPriceFor($organization->currency);

        $current = Subscription::where('organization_id', $organization->id)
            ->where('status', 'active')->latest()->first();

        // Trial: first paid subscription on a plan that offers one — no charge now.
        $startTrial = $plan->trial_days > 0 && $base > 0
            && ! Subscription::where('organization_id', $organization->id)->exists();

        // Proration: credit the unused value of the current paid plan against the new charge.
        $prorationCredit = $this->prorationCredit($current, $organization);

        $discount = ($coupon && $coupon->isRedeemable()) ? $coupon->discountFor($base) : 0;
        $amount = $startTrial ? 0 : max(0, $base - $discount - $prorationCredit);

        return DB::transaction(function () use ($organization, $plan, $interval, $amount, $coupon, $startTrial) {
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

            if ($coupon && $coupon->isRedeemable()) {
                $coupon->increment('times_redeemed');
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
                    'cancel_at_period_end' => false,
                    'trial_ends_at' => $startTrial ? now()->addDays($plan->trial_days) : null,
                    'payment_attempts' => 0,
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

    /**
     * Cancel at period end — the org keeps its plan and access until
     * current_period_end (grace period), then downgrades automatically.
     */
    public function cancel(Organization $organization): void
    {
        Subscription::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->update(['cancel_at_period_end' => true, 'canceled_at' => now()]);

        $this->activity->log('subscription.canceled');
    }

    /** Downgrade subscriptions whose grace period has ended (scheduler). */
    public function downgradeExpired(): int
    {
        $free = Plan::where('key', 'free')->first();
        $expired = Subscription::where('cancel_at_period_end', true)
            ->where('status', 'active')
            ->whereNotNull('current_period_end')
            ->where('current_period_end', '<=', now())
            ->get();

        foreach ($expired as $subscription) {
            $subscription->update(['status' => 'canceled']);
            if ($free) {
                Organization::whereKey($subscription->organization_id)->update(['plan_id' => $free->id]);
            }
        }

        return $expired->count();
    }

    /**
     * Handle a failed renewal payment: increment attempts, enter dunning
     * (past_due), and after the final attempt, downgrade to free.
     */
    public function recordPaymentFailure(Subscription $subscription, int $maxAttempts = 3): void
    {
        $attempts = $subscription->payment_attempts + 1;
        $subscription->update([
            'payment_attempts' => $attempts,
            'status' => $attempts >= $maxAttempts ? 'canceled' : 'past_due',
        ]);

        if ($attempts >= $maxAttempts) {
            $free = Plan::where('key', 'free')->first();
            if ($free) {
                Organization::whereKey($subscription->organization_id)->update(['plan_id' => $free->id]);
            }
        }

        $this->activity->log('subscription.payment_failed', $subscription, ['attempt' => $attempts]);
    }

    /** Record a refund against an invoice. */
    public function refund(Invoice $invoice, ?int $amount = null, ?string $reason = null): Refund
    {
        $amount ??= $invoice->total;

        $refund = Refund::create([
            'invoice_id' => $invoice->id,
            'organization_id' => $invoice->organization_id,
            'amount' => $amount,
            'currency' => $invoice->currency,
            'reason' => $reason,
        ]);

        $invoice->update(['status' => 'refunded']);
        $this->activity->log('invoice.refunded', $invoice, ['amount' => $amount]);

        return $refund;
    }

    /** Unused value of the current paid plan for the remaining period (proration). */
    private function prorationCredit(?Subscription $current, Organization $organization): int
    {
        if (! $current || ! $current->current_period_end || ! $current->plan_id) {
            return 0;
        }

        $plan = Plan::find($current->plan_id);
        if (! $plan) {
            return 0;
        }

        $periodPrice = $current->interval === 'yearly'
            ? $plan->yearlyPriceFor($organization->currency)
            : $plan->monthlyPriceFor($organization->currency);

        if ($periodPrice <= 0) {
            return 0;
        }

        $start = $current->current_period_start ?? $current->created_at;
        $totalDays = max(1, $start->diffInDays($current->current_period_end));
        $remainingDays = max(0, now()->diffInDays($current->current_period_end, false));

        return (int) round($periodPrice * ($remainingDays / $totalDays));
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
