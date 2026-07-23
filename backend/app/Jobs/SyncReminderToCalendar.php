<?php

namespace App\Jobs;

use App\Contracts\CalendarProvider;
use App\Models\CalendarConnection;
use App\Models\CalendarEvent;
use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Retry-safe calendar sync for a single reminder + connection. Deduplicates via
 * the calendar_events table, refreshes expired tokens, and logs every outcome.
 * Calendar failures NEVER block the core reminder workflow — this runs on the
 * queue and only writes sync logs.
 */
class SyncReminderToCalendar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $reminderId,
        public string $connectionId,
        public string $action = 'create',
    ) {}

    public function handle(CalendarProvider $provider): void
    {
        $connection = CalendarConnection::find($this->connectionId);
        $reminder = Reminder::withoutGlobalScope('organization')->find($this->reminderId);

        if (! $connection || ! $reminder || ! $connection->sync_enabled) {
            $this->log($connection, $reminder, 'skipped', 'missing_or_disabled');

            return;
        }

        // Disabled provider (no credentials) → record a skip, never a fake success.
        if (! $provider->isConfigured()) {
            $this->log($connection, $reminder, 'skipped', 'provider_not_configured');

            return;
        }

        if ($connection->tokenExpired()) {
            $provider->refreshToken($connection);
        }

        // Dedupe: reuse the stored external id if we've synced this pair before.
        $existing = CalendarEvent::where('reminder_id', $reminder->id)
            ->where('calendar_connection_id', $connection->id)
            ->first();

        if ($this->action === 'delete') {
            if ($existing) {
                $provider->deleteEvent($connection, $existing->external_event_id);
                $existing->delete();
            }
            $this->log($connection, $reminder, 'success', 'delete');

            return;
        }

        $externalId = $provider->pushEvent($connection, $reminder, $existing?->external_event_id);

        if ($externalId && ! $existing) {
            CalendarEvent::create([
                'reminder_id' => $reminder->id,
                'calendar_connection_id' => $connection->id,
                'external_event_id' => $externalId,
            ]);
        }

        $this->log($connection, $reminder, 'success', $this->action);
    }

    public function failed(Throwable $e): void
    {
        DB::table('calendar_sync_logs')->insert([
            'id' => (string) Str::uuid7(),
            'calendar_connection_id' => $this->connectionId,
            'reminder_id' => $this->reminderId,
            'status' => 'failed',
            'action' => $this->action,
            'error' => Str::limit($e->getMessage(), 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function log(?CalendarConnection $connection, ?Reminder $reminder, string $status, string $action): void
    {
        DB::table('calendar_sync_logs')->insert([
            'id' => (string) Str::uuid7(),
            'calendar_connection_id' => $connection?->id,
            'reminder_id' => $reminder?->id,
            'status' => $status,
            'action' => $action,
            'error' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
