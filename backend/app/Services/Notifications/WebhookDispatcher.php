<?php

namespace App\Services\Notifications;

use App\Jobs\DispatchWebhook;
use App\Models\WebhookEndpoint;
use App\Support\Tenancy;

class WebhookDispatcher
{
    /** Fan a domain event out to every subscribed endpoint in the current tenant. */
    public function dispatch(string $event, array $payload): void
    {
        $endpoints = WebhookEndpoint::where('organization_id', Tenancy::currentId())
            ->where('is_active', true)
            ->get()
            ->filter(fn (WebhookEndpoint $e) => $e->subscribesTo($event));

        foreach ($endpoints as $endpoint) {
            $delivery = $endpoint->deliveries()->create([
                'event' => $event,
                'payload' => $payload,
                'status' => 'pending',
            ]);

            DispatchWebhook::dispatch($delivery->id);
        }
    }
}
