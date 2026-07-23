<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the application locale for the request so API messages, emails, and
 * notifications are localized. Precedence: authenticated user's locale, then the
 * Accept-Language header, then the app default. Only supported locales are honored.
 */
class SetLocale
{
    private const SUPPORTED = ['ar', 'en', 'es', 'tr'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale
            ?? $this->fromHeader($request->header('Accept-Language'))
            ?? config('app.locale');

        if (in_array($locale, self::SUPPORTED, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    private function fromHeader(?string $header): ?string
    {
        if (! $header) {
            return null;
        }

        // Take the primary language subtag of the first entry (e.g. "ar-SA,en" → "ar").
        $primary = strtolower(substr(trim(explode(',', $header)[0]), 0, 2));

        return in_array($primary, self::SUPPORTED, true) ? $primary : null;
    }
}
