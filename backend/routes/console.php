<?php

use App\Services\Payments\BillingService;
use Illuminate\Support\Facades\Schedule;

// Dispatch due reminder notifications every minute.
Schedule::command('reminders:dispatch-due')->everyMinute()->withoutOverlapping();

// Downgrade subscriptions whose cancellation grace period has ended (hourly).
Schedule::call(function () {
    app(BillingService::class)->downgradeExpired();
})->hourly()->name('billing:downgrade-expired')->withoutOverlapping();
