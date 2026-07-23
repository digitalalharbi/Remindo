<?php

namespace App\Jobs;

use App\Models\NotificationPreference;
use App\Models\ReminderNotification;
use App\Notifications\ReminderDueNotification;
use App\Services\Notifications\CreditService;
use App\Support\Tenancy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Delivers a single reminder notification over its channel. Channels that
 * require paid credits or external providers (sms, whatsapp, web_push, webhook)
 * run through replaceable adapters; email + in_app are delivered directly.
 */
class SendReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $notificationId) {}

    public function handle(): void
    {
        $notification = ReminderNotification::with('reminder.organization', 'reminder.assignee', 'reminder.creator')
            ->find($this->notificationId);

        if (! $notification || ! $notification->reminder) {
            return;
        }

        $reminder = $notification->reminder;

        // Skip if the reminder is no longer active or is snoozed past this send time.
        if ($reminder->status !== 'active') {
            return;
        }
        if ($reminder->snoozed_until && $reminder->snoozed_until->isFuture()) {
            return;
        }

        $recipient = $reminder->assignee ?? $reminder->creator ?? $reminder->organization?->owner;
        if (! $recipient) {
            return;
        }

        $channel = $notification->channel;
        $pref = NotificationPreference::firstOrCreate(['user_id' => $recipient->id]);

        // Respect channel preferences.
        if (! $pref->allows($channel)) {
            $notification->update(['status' => 'cancelled', 'error' => 'channel_disabled']);

            return;
        }

        // Quiet hours suppress interruptive channels (email + in-app still deliver).
        $localHour = (int) now()->timezone($recipient->timezone ?? 'UTC')->format('G');
        $interruptive = in_array($channel, ['web_push', 'sms', 'whatsapp'], true);
        if ($interruptive && $pref->isQuietAt($localHour)) {
            $notification->update(['status' => 'cancelled', 'error' => 'quiet_hours']);

            return;
        }

        // Metered channels need credits; block (never negative) when insufficient.
        if (in_array($channel, ['sms', 'whatsapp'], true)) {
            $credits = app(CreditService::class);
            $org = $reminder->organization;
            if (! $credits->deduct($org, $channel, 1, $reminder->id)) {
                $notification->update(['status' => 'failed', 'error' => 'insufficient_credits']);

                return;
            }
        }

        Tenancy::forOrganization($reminder->organization_id, function () use ($recipient, $reminder, $channel) {
            // email + in_app deliver directly; sms/whatsapp/web_push/webhook run through
            // their adapters (mock/disabled until real credentials are supplied).
            $recipient->notify(new ReminderDueNotification($reminder, $channel));
        });
    }

    public function failed(Throwable $e): void
    {
        ReminderNotification::where('id', $this->notificationId)->update([
            'status' => 'failed',
            'error' => mb_substr($e->getMessage(), 0, 1000),
        ]);
    }
}
