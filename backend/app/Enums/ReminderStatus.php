<?php

namespace App\Enums;

enum ReminderStatus: string
{
    case Active = 'active';
    case Snoozed = 'snoozed';
    case Completed = 'completed';
    case Renewed = 'renewed';
    case Archived = 'archived';
    case Cancelled = 'cancelled';
}
