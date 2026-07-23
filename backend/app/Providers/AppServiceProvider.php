<?php

namespace App\Providers;

use App\Contracts\DocumentExtractor;
use App\Services\AI\MockDocumentExtractor;
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
    }

    public function boot(): void
    {
        //
    }
}
