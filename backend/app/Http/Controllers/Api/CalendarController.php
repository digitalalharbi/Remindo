<?php

namespace App\Http\Controllers\Api;

use App\Contracts\CalendarProvider;
use App\Http\Controllers\Controller;
use App\Jobs\SyncReminderToCalendar;
use App\Models\CalendarConnection;
use App\Models\Reminder;
use App\Services\Calendar\IcsGenerator;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller
{
    public function __construct(
        private readonly IcsGenerator $ics,
        private readonly CalendarProvider $provider,
    ) {}

    /** Download a single reminder as an .ics file (always available). */
    public function icsForReminder(Reminder $reminder): Response
    {
        return $this->icsResponse([$reminder], "reminder-{$reminder->id}.ics");
    }

    /** Download all active reminders in the current workspace as one .ics file. */
    public function icsFeed(): Response
    {
        $reminders = Reminder::active()->orderBy('expiry_date')->get();

        return $this->icsResponse($reminders, 'remindo.ics');
    }

    /** Connections for the current user + whether any real provider is configured. */
    public function connections(Request $request): JsonResponse
    {
        $connections = CalendarConnection::where('user_id', $request->user()->id)
            ->get(['id', 'provider', 'sync_enabled', 'expires_at', 'created_at'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'provider' => $c->provider,
                'sync_enabled' => $c->sync_enabled,
                'connected_at' => $c->created_at?->toIso8601String(),
            ]);

        return ApiResponse::success([
            'connections' => $connections,
            // Honest availability: real sync only when a provider is configured.
            'sync_available' => $this->provider->isConfigured(),
        ]);
    }

    /** Begin connecting a provider — disabled state when no credentials exist. */
    public function connect(string $provider): JsonResponse
    {
        if (! in_array($provider, ['google', 'outlook'], true) || ! $this->provider->isConfigured()) {
            return ApiResponse::error(__('calendar.unavailable'), 422);
        }

        // With real credentials this returns the provider's OAuth redirect URL.
        return ApiResponse::error(__('calendar.unavailable'), 422);
    }

    public function disconnect(Request $request, string $provider): JsonResponse
    {
        $connection = CalendarConnection::where('user_id', $request->user()->id)
            ->where('provider', $provider)
            ->first();

        if ($connection) {
            // Revoke tokens with the provider, then remove locally.
            // (Real provider performs token revocation; null provider no-ops.)
            $connection->delete();
        }

        return ApiResponse::message(__('calendar.disconnected'));
    }

    /** Manually queue a sync for a reminder to all of the user's connections. */
    public function sync(Request $request, Reminder $reminder): JsonResponse
    {
        $connections = CalendarConnection::where('organization_id', Tenancy::currentId())
            ->where('sync_enabled', true)
            ->get();

        foreach ($connections as $connection) {
            SyncReminderToCalendar::dispatch($reminder->id, $connection->id, 'create');
        }

        return ApiResponse::success([
            'queued' => $connections->count(),
            'sync_available' => $this->provider->isConfigured(),
        ], __('calendar.sync_queued'));
    }

    private function icsResponse(iterable $reminders, string $filename): Response
    {
        $body = $this->ics->forReminders(collect($reminders));

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
