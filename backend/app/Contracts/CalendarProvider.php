<?php

namespace App\Contracts;

use App\Models\CalendarConnection;
use App\Models\Reminder;

/**
 * Provider-agnostic calendar sync. Real providers (Google, Outlook) push events
 * over their APIs; the null provider is used when no credentials are configured.
 * Implementations must be idempotent (safe to retry) and must refresh expired
 * tokens themselves.
 */
interface CalendarProvider
{
    public function isConfigured(): bool;

    /** Create/update the event for a reminder; returns the external event id or null. */
    public function pushEvent(CalendarConnection $connection, Reminder $reminder, ?string $externalId = null): ?string;

    public function deleteEvent(CalendarConnection $connection, string $externalId): void;

    /** Refresh an expired access token in place. */
    public function refreshToken(CalendarConnection $connection): void;
}
