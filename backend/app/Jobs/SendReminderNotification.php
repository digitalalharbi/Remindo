<?php

namespace App\Jobs;

use App\Models\ReminderNotification;
use App\Notifications\ReminderDueNotification;
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

        Tenancy::forOrganization($reminder->organization_id, function () use ($recipient, $reminder, $notification) {
            // For MVP, email + in_app go through Laravel's notification system.
            // Other channels are delegated to their adapters (mock by default).
            $recipient->notify(new ReminderDueNotification($reminder, $notification->channel));
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
