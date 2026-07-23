<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Delivers a signed webhook with retries and a persistent delivery record.
 * The payload is signed with HMAC-SHA256 using the endpoint secret; receivers
 * verify via the X-Remindo-Signature header.
 */
class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public array $backoff = [10, 30, 120, 300];

    public function __construct(public string $deliveryId) {}

    public function handle(): void
    {
        $delivery = WebhookDelivery::find($this->deliveryId);
        if (! $delivery) {
            return;
        }

        $endpoint = WebhookEndpoint::find($delivery->webhook_endpoint_id);
        if (! $endpoint || ! $endpoint->is_active) {
            $delivery->update(['status' => 'failed', 'attempts' => $delivery->attempts + 1]);

            return;
        }

        $body = json_encode([
            'event' => $delivery->event,
            'data' => $delivery->payload,
            'delivery_id' => $delivery->id,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $signature = hash_hmac('sha256', $body, $endpoint->secret);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Remindo-Event' => $delivery->event,
            'X-Remindo-Signature' => 'sha256='.$signature,
            'X-Remindo-Delivery' => $delivery->id,
        ])->timeout(10)->withBody($body, 'application/json')->post($endpoint->url);

        $delivery->update([
            'status' => $response->successful() ? 'success' : 'failed',
            'response_code' => $response->status(),
            'attempts' => $delivery->attempts + 1,
            'delivered_at' => $response->successful() ? now() : null,
        ]);

        if (! $response->successful()) {
            $this->fail(new \RuntimeException("Webhook returned {$response->status()}"));
        }
    }

    public function failed(Throwable $e): void
    {
        WebhookDelivery::where('id', $this->deliveryId)->update(['status' => 'failed']);
    }
}
