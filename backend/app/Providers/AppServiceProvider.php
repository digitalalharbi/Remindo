<?php

namespace App\Providers;

use App\Contracts\DocumentExtractor;
use App\Contracts\PaymentGateway;
use App\Services\AI\MockDocumentExtractor;
use App\Services\Payments\SandboxGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Provider-agnostic AI extraction. Selected by config('ai.provider').
        // Only the replaceable mock adapter ships by default; live providers are
        // wired here once their credentials exist (they are not claimed to work
        // in production until then).
        $this->app->bind(DocumentExtractor::class, function () {
            return match (config('ai.provider')) {
                // 'openai' => new OpenAiDocumentExtractor(...),
                // 'anthropic' => new AnthropicDocumentExtractor(...),
                default => new MockDocumentExtractor,
            };
        });

        // Provider-agnostic payments. Selected by config('services.payments.provider').
        // Real gateways (Moyasar/Tap/Stripe) are wired here once credentials exist;
        // the sandbox adapter runs the flow end-to-end until then (never live).
        $this->app->bind(PaymentGateway::class, function () {
            return match (config('services.payments.provider')) {
                // 'moyasar' => new MoyasarGateway(...),
                // 'tap' => new TapGateway(...),
                // 'stripe' => new StripeGateway(...),
                default => new SandboxGateway,
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
