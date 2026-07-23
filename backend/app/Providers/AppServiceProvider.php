<?php

namespace App\Providers;

use App\Contracts\CalendarProvider;
use App\Contracts\DocumentExtractor;
use App\Contracts\PaymentGateway;
use App\Services\AI\MockDocumentExtractor;
use App\Services\Calendar\NullCalendarProvider;
use App\Services\Payments\SandboxGateway;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\Provider;

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

        // Calendar sync provider. Real Google/Outlook adapters are bound here once
        // credentials exist; until then the null provider keeps sync safely inert.
        $this->app->bind(CalendarProvider::class, function () {
            return match (config('services.calendar.provider')) {
                // 'google' => new GoogleCalendarProvider(...),
                // 'outlook' => new OutlookCalendarProvider(...),
                default => new NullCalendarProvider,
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
        // Register the Microsoft OAuth provider (SocialiteProviders). Google is
        // built into Socialite. Providers stay inert until credentials are set.
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('microsoft', Provider::class);
        });
    }
}
