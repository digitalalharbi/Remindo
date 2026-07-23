<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasUuids;

    protected $fillable = ['reminder_id', 'calendar_connection_id', 'external_event_id'];
}
