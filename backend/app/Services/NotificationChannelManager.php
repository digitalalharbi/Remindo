<?php

namespace App\Services;

use App\Mail\ReminderDueMail;
use App\Models\ReminderSchedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationChannelManager
{
    public function send(ReminderSchedule $schedule): array
    {
        $reminder = $schedule->reminder;
        $user = $reminder->user;
        $subject = __('Reminder: :name', ['name' => $reminder->name], $user->locale);
        $body = __(':name expires on :date.', [
            'name' => $reminder->name,
            'date' => $reminder->expires_at->format('Y-m-d'),
        ], $user->locale);

        if ($schedule->channel === 'email') {
            Mail::to($user->email)->send(new ReminderDueMail($subject, $body));

            return compact('subject', 'body') + ['provider' => config('mail.default')];
        }

        if ($schedule->channel === 'in_app') {
            return compact('subject', 'body') + ['provider' => 'database'];
        }

        Log::info('Mock notification dispatched', [
            'channel' => $schedule->channel,
            'schedule_id' => $schedule->id,
            'recipient' => $user->id,
        ]);

        return compact('subject', 'body') + ['provider' => 'mock'];
    }
}
