<?php

namespace App\Notifications;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Reminder $reminder,
        public string $channel = 'email',
    ) {}

    /**
     * email + in_app are delivered here. Other channels (sms, whatsapp, web_push,
     * webhook) are handled by their adapters; we still record an in-app copy so
     * the notification center always reflects what was sent.
     */
    public function via(object $notifiable): array
    {
        return match ($this->channel) {
            'email' => ['mail', 'database'],
            'in_app' => ['database'],
            default => ['database'], // adapter-backed channels log an in-app copy
        };
    }

    public function toMail(object $notifiable): MailMessage
    {
        $days = $this->reminder->daysUntilExpiry();
        $when = $days > 0
            ? __('notifications.days_left', ['days' => $days])
            : __('notifications.due_today');

        return (new MailMessage)
            ->subject(__('notifications.subject', ['title' => $this->reminder->title]))
            ->greeting(__('notifications.greeting', ['name' => $notifiable->name]))
            ->line($when)
            ->line(__('notifications.expires_on', ['date' => $this->reminder->expiry_date->toDateString()]))
            ->action(__('notifications.view_reminder'), config('app.frontend_url', 'http://localhost:3000').'/app/reminders/'.$this->reminder->id);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'reminder_id' => $this->reminder->id,
            'title' => $this->reminder->title,
            'expiry_date' => $this->reminder->expiry_date->toDateString(),
            'days_until_expiry' => $this->reminder->daysUntilExpiry(),
            'channel' => $this->channel,
        ];
    }
}
