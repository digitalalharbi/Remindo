<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payments\BillingService;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class SubscriptionController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    /** Current subscription + plan + recent invoices for the active organization. */
    public function show(): JsonResponse
    {
        $organization = Tenancy::current()->load('plan');

        $subscription = Subscription::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $invoices = Invoice::where('organization_id', $organization->id)
            ->latest('issued_at')
            ->limit(12)
            ->get()
            ->map(fn (Invoice $i) => [
                'id' => $i->id,
                'number' => $i->number,
                'status' => $i->status,
                'total' => $i->total,
                'currency' => $i->currency,
                'issued_at' => $i->issued_at?->toDateString(),
            ]);

        return ApiResponse::success([
            'plan' => new PlanResource($organization->plan),
            'currency' => $organization->currency,
            'subscription' => $subscription ? [
                'interval' => $subscription->interval,
                'status' => $subscription->status,
                'current_period_end' => $subscription->current_period_end?->toDateString(),
            ] : null,
            'invoices' => $invoices,
        ]);
    }

    /** Subscribe to / change plan (monthly or yearly). Sandbox charge by default. */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_key' => ['required', Rule::exists('plans', 'key')->where('is_active', true)],
            'interval' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        $organization = Tenancy::current();
        $plan = Plan::where('key', $data['plan_key'])->firstOrFail();

        try {
            $this->billing->subscribe($organization, $plan, $data['interval']);
        } catch (RuntimeException $e) {
            return ApiResponse::error(__('billing.payment_failed'), 402);
        }

        return ApiResponse::success(
            new PlanResource($plan),
            __('billing.subscribed'),
        );
    }

    public function cancel(): JsonResponse
    {
        $this->billing->cancel(Tenancy::current());

        return ApiResponse::message(__('billing.canceled'));
    }
}
