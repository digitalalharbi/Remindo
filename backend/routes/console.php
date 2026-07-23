<?php

use Illuminate\Support\Facades\Schedule;

// Dispatch due reminder notifications every minute.
Schedule::command('reminders:dispatch-due')->everyMinute()->withoutOverlapping();
