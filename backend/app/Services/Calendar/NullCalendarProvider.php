<?php

namespace App\Services\Calendar;

use App\Contracts\CalendarProvider;
use App\Models\CalendarConnection;
use App\Models\Reminder;

/**
 * Disabled/sandbox calendar provider used when no real Google/Outlook
 * credentials are configured. It performs NO external delivery and never
 * pretends to — the sync job records these attempts as "skipped". Swapping in a
 * real provider is a binding change once credentials exist.
 */
class NullCalendarProvider implements CalendarProvider
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function pushEvent(CalendarConnection $connection, Reminder $reminder, ?string $externalId = null): ?string
    {
        return null; // no external event created
    }

    public function deleteEvent(CalendarConnection $connection, string $externalId): void
    {
        // no-op
    }

    public function refreshToken(CalendarConnection $connection): void
    {
        // no-op
    }
}
