<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\Payments\BillingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Inbound payment-gateway webhooks. Processing is IDEMPOTENT — a replayed event
 * (same provider + event id) is a no-op. This is the trusted signal that
 * confirms/updates a subscription: the app never activates a paid plan from the
 * checkout return page alone.
 */
class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $provider, BillingService $billing): JsonResponse
    {
        // Real gateways: verify the signature here before trusting the payload.
        $this->verifySignature($request, $provider);

        $eventId = (string) ($request->input('id') ?: $request->header('X-Event-Id') ?: Str::uuid7());
        $type = (string) $request->input('type', 'unknown');

        // Idempotency: record-or-skip.
        $isNew = DB::table('processed_webhooks')->insertOrIgnore([
            'id' => (string) Str::uuid7(),
            'provider' => $provider,
            'event_id' => $eventId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! $isNew) {
            return ApiResponse::success(['duplicate' => true]);
        }

        $subscriptionId = $request->input('data.subscription_id');
        $subscription = $subscriptionId ? Subscription::find($subscriptionId) : null;

        if ($subscription) {
            match ($type) {
                'payment.succeeded', 'invoice.paid' => $subscription->update([
                    'status' => 'active',
                    'payment_attempts' => 0,
                ]),
                'payment.failed' => $billing->recordPaymentFailure($subscription),
                default => null,
            };
        }

        return ApiResponse::success(['processed' => true, 'event' => $type]);
    }

    private function verifySignature(Request $request, string $provider): void
    {
        $secret = config("services.payments.{$provider}.webhook_secret");
        if (! $secret) {
            return; // sandbox / not configured — nothing to verify
        }

        $signature = $request->header('X-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        abort_unless(hash_equals($expected, $signature), 403, 'Invalid webhook signature');
    }
}
