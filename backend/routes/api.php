<?php

use App\Http\Controllers\Api\ReminderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'data' => ['service' => 'remindo-api', 'status' => 'ok'],
    ]));

    Route::apiResource('reminders', ReminderController::class);
});
