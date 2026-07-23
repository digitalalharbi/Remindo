<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReminderNotification extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'reminder_id', 'reminder_schedule_id', 'channel',
        'subject', 'body', 'read_at', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'sent_at' => 'datetime'];
    }
}
