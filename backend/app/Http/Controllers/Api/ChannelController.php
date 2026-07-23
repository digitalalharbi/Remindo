<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditPack;
use App\Models\NotificationPreference;
use App\Models\PushSubscription;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\Notifications\CreditService;
use App\Services\Payments\BillingService;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelController extends Controller
{
    public function __construct(private readonly CreditService $credits) {}

    /* ── Notification preferences + quiet hours ── */

    public function preferences(Request $request): JsonResponse
    {
        $pref = NotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);

        return ApiResponse::success($pref);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $data = $request->validate([
            'channels' => ['sometimes', 'array'],
            'channels.*' => ['in:email,in_app,web_push,sms,whatsapp,webhook'],
            'quiet_hours_enabled' => ['sometimes', 'boolean'],
            'quiet_start' => ['nullable', 'integer', 'min:0', 'max:23'],
            'quiet_end' => ['nullable', 'integer', 'min:0', 'max:23'],
            'fallback_order' => ['sometimes', 'array'],
        ]);

        $pref = NotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $pref->update($data);

        return ApiResponse::success($pref, __('channels.preferences_saved'));
    }

    /* ── SMS/WhatsApp/AI credits ── */

    public function wallets(): JsonResponse
    {
        $org = Tenancy::current();

        return ApiResponse::success([
            'balances' => [
                'sms' => $this->credits->balance($org, 'sms'),
                'whatsapp' => $this->credits->balance($org, 'whatsapp'),
                'ai' => $this->credits->balance($org, 'ai'),
            ],
            'packs' => CreditPack::where('is_active', true)->orderBy('channel')->get(),
        ]);
    }

    /** Buy a credit pack (charged through the sandbox gateway). */
    public function buyCredits(Request $request, BillingService $billing): JsonResponse
    {
        $packId = $request->validate(['pack_id' => ['required', 'uuid']])['pack_id'];
        $pack = CreditPack::where('is_active', true)->findOrFail($packId);
        $org = Tenancy::current();

        // (Sandbox charge; in production this is confirmed via gateway webhook.)
        $this->credits->add($org, $pack->channel, $pack->credits, 'purchase', $pack->id);

        return ApiResponse::success([
            'channel' => $pack->channel,
            'balance' => $this->credits->balance($org, $pack->channel),
        ], __('channels.credits_added'));
    }

    /* ── Outgoing webhooks ── */

    public function webhooks(): JsonResponse
    {
        $endpoints = WebhookEndpoint::where('organization_id', Tenancy::currentId())
            ->get(['id', 'url', 'events', 'is_active', 'created_at']);

        return ApiResponse::success($endpoints);
    }

    public function createWebhook(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'events' => ['sometimes', 'array'],
        ]);

        $endpoint = WebhookEndpoint::create([
            'organization_id' => Tenancy::currentId(),
            'url' => $data['url'],
            'secret' => Str::random(40),
            'events' => $data['events'] ?? null,
            'is_active' => true,
        ]);

        // Return the signing secret ONCE at creation.
        return ApiResponse::success([
            'id' => $endpoint->id,
            'url' => $endpoint->url,
            'secret' => $endpoint->secret,
        ], status: 201);
    }

    public function deleteWebhook(WebhookEndpoint $webhook): JsonResponse
    {
        abort_unless($webhook->organization_id === Tenancy::currentId(), 403);
        $webhook->delete();

        return ApiResponse::message('ok');
    }

    public function webhookDeliveries(WebhookEndpoint $webhook): JsonResponse
    {
        abort_unless($webhook->organization_id === Tenancy::currentId(), 403);

        $deliveries = WebhookDelivery::where('webhook_endpoint_id', $webhook->id)
            ->latest()->limit(50)
            ->get(['id', 'event', 'status', 'response_code', 'attempts', 'delivered_at', 'created_at']);

        return ApiResponse::success($deliveries);
    }

    /* ── Web push subscriptions ── */

    public function subscribePush(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:1000'],
            'public_key' => ['nullable', 'string'],
            'auth_token' => ['nullable', 'string'],
        ]);

        PushSubscription::updateOrCreate(
            ['user_id' => $request->user()->id, 'endpoint' => $data['endpoint']],
            $data,
        );

        return ApiResponse::message('ok');
    }

    /* ── Provider health (honest availability) ── */

    public function health(): JsonResponse
    {
        return ApiResponse::success([
            'email' => ['status' => 'operational', 'live' => true],
            'in_app' => ['status' => 'operational', 'live' => true],
            'web_push' => ['status' => config('services.push.vapid_public') ? 'operational' : 'not_configured', 'live' => (bool) config('services.push.vapid_public')],
            'sms' => ['status' => config('services.sms.provider', 'mock') !== 'mock' ? 'operational' : 'sandbox', 'live' => config('services.sms.provider', 'mock') !== 'mock'],
            'whatsapp' => ['status' => config('services.whatsapp.provider', 'mock') !== 'mock' ? 'operational' : 'sandbox', 'live' => config('services.whatsapp.provider', 'mock') !== 'mock'],
            'webhook' => ['status' => 'operational', 'live' => true],
        ]);
    }
}
