<?php

namespace App\Services;

use App\Models\Reminder;
use App\Services\Notifications\WebhookDispatcher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * All reminder business logic. Controllers stay thin and delegate here.
 * Handles creation with notification schedules, updates, and the lifecycle
 * transitions (complete, renew, snooze, archive).
 */
class ReminderService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly WebhookDispatcher $webhooks,
    ) {}

    /**
     * Create a reminder plus its notification schedule.
     *
     * @param  array  $data  validated payload (see StoreReminderRequest)
     */
    public function create(array $data): Reminder
    {
        return DB::transaction(function () use ($data) {
            $reminder = Reminder::create([
                'created_by' => $data['created_by'] ?? auth()->id(),
                'assigned_to' => $data['assigned_to'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'document_id' => $data['document_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'issuer' => $data['issuer'] ?? null,
                'expiry_date' => $data['expiry_date'],
                'issue_date' => $data['issue_date'] ?? null,
                'recurrence' => $data['recurrence'] ?? 'none',
                'recurrence_interval' => $data['recurrence_interval'] ?? null,
                'recurrence_unit' => $data['recurrence_unit'] ?? null,
                'meta' => $data['meta'] ?? null,
            ]);

            if (! empty($data['tags'])) {
                $reminder->tags()->sync($data['tags']);
            }

            $this->syncNotifications(
                $reminder,
                offsets: $data['reminder_offsets'] ?? [7],
                channels: $data['channels'] ?? ['email', 'in_app'],
                remindAtTime: $data['remind_at_time'] ?? '09:00',
            );

            $this->activity->log('reminder.created', $reminder);
            $this->webhooks->dispatch('reminder.created', [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'expiry_date' => $reminder->expiry_date->toDateString(),
            ]);

            return $reminder->fresh(['category', 'tags', 'notifications']);
        });
    }

    public function update(Reminder $reminder, array $data): Reminder
    {
        return DB::transaction(function () use ($reminder, $data) {
            $reminder->fill(array_filter([
                'assigned_to' => $data['assigned_to'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'issuer' => $data['issuer'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'issue_date' => $data['issue_date'] ?? null,
                'recurrence' => $data['recurrence'] ?? null,
                'recurrence_interval' => $data['recurrence_interval'] ?? null,
                'recurrence_unit' => $data['recurrence_unit'] ?? null,
            ], fn ($v) => ! is_null($v)));
            $reminder->save();

            if (array_key_exists('tags', $data)) {
                $reminder->tags()->sync($data['tags'] ?? []);
            }

            // If timing inputs changed, regenerate the pending schedule.
            if (isset($data['reminder_offsets']) || isset($data['channels']) || isset($data['expiry_date'])) {
                $reminder->notifications()->where('status', 'pending')->delete();
                $this->syncNotifications(
                    $reminder,
                    offsets: $data['reminder_offsets'] ?? $reminder->notifications->pluck('offset_days')->unique()->all() ?: [7],
                    channels: $data['channels'] ?? $reminder->notifications->pluck('channel')->unique()->all() ?: ['email', 'in_app'],
                    remindAtTime: $data['remind_at_time'] ?? '09:00',
                );
            }

            $this->activity->log('reminder.updated', $reminder);

            return $reminder->fresh(['category', 'tags', 'notifications']);
        });
    }

    /** Mark a reminder as completed (no renewal). */
    public function complete(Reminder $reminder): Reminder
    {
        $reminder->update(['status' => 'completed', 'completed_at' => now()]);
        $reminder->notifications()->where('status', 'pending')->update(['status' => 'cancelled']);
        $this->activity->log('reminder.completed', $reminder);

        return $reminder;
    }

    /**
     * Renew a reminder to a new expiry date. Keeps the same schedule shape and
     * regenerates future notifications against the new date.
     */
    public function renew(Reminder $reminder, string $newExpiryDate): Reminder
    {
        return DB::transaction(function () use ($reminder, $newExpiryDate) {
            $offsets = $reminder->notifications->pluck('offset_days')->unique()->values()->all() ?: [7];
            $channels = $reminder->notifications->pluck('channel')->unique()->values()->all() ?: ['email', 'in_app'];

            $reminder->update([
                'status' => 'active',
                'expiry_date' => $newExpiryDate,
                'issue_date' => $reminder->expiry_date, // previous expiry becomes the new issue date
                'completed_at' => null,
                'snoozed_until' => null,
            ]);

            $reminder->notifications()->where('status', 'pending')->delete();
            $this->syncNotifications($reminder, $offsets, $channels);

            $this->activity->log('reminder.renewed', $reminder, ['new_expiry_date' => $newExpiryDate]);

            return $reminder->fresh(['category', 'tags', 'notifications']);
        });
    }

    public function snooze(Reminder $reminder, string $until): Reminder
    {
        $reminder->update(['snoozed_until' => $until]);
        $this->activity->log('reminder.snoozed', $reminder, ['until' => $until]);

        return $reminder;
    }

    public function archive(Reminder $reminder): Reminder
    {
        $reminder->update(['status' => 'archived']);
        $reminder->notifications()->where('status', 'pending')->update(['status' => 'cancelled']);
        $this->activity->log('reminder.archived', $reminder);

        return $reminder;
    }

    /**
     * Build notification rows: one per (offset × channel). send_at is the expiry
     * date minus the offset, at the requested local time. Past send_at values are
     * skipped so we never schedule a notification in the past.
     */
    private function syncNotifications(Reminder $reminder, array $offsets, array $channels, string $remindAtTime = '09:00'): void
    {
        [$hour, $minute] = array_pad(explode(':', $remindAtTime), 2, 0);
        $expiry = CarbonImmutable::parse($reminder->expiry_date);

        foreach (array_unique($offsets) as $offset) {
            $sendAt = $expiry->subDays((int) $offset)->setTime((int) $hour, (int) $minute);

            foreach (array_unique($channels) as $channel) {
                $reminder->notifications()->create([
                    'offset_days' => (int) $offset,
                    'channel' => $channel,
                    'send_at' => $sendAt,
                    // If the send time already passed, mark cancelled rather than firing late.
                    'status' => $sendAt->isPast() ? 'cancelled' : 'pending',
                ]);
            }
        }
    }
}
