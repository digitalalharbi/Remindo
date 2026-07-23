<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLoginNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $ip,
        public string $userAgent,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('auth.new_login_subject'))
            ->line(__('auth.new_login_line'))
            ->line(__('auth.new_login_ip', ['ip' => $this->ip]))
            ->line(__('auth.new_login_hint'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_login',
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
        ];
    }
}
