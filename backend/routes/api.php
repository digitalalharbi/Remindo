<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ReminderController;
use App\Models\ReminderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json(['data' => ['service' => 'remindo-api', 'status' => 'ok']]));

    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/forgot-password', [PasswordController::class, 'forgot']);
        Route::post('/auth/reset-password', [PasswordController::class, 'reset']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/sessions', [AuthController::class, 'sessions']);
        Route::delete('/auth/sessions/{token}', [AuthController::class, 'revokeSession']);
        Route::put('/auth/password', [PasswordController::class, 'change']);
        Route::post('/auth/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1');
        Route::get('/auth/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');

        Route::apiResource('reminders', ReminderController::class);
        Route::post('/reminders/{reminder}/archive', [ReminderController::class, 'archive']);
        Route::post('/reminders/{reminder}/snooze', [ReminderController::class, 'snooze']);
        Route::post('/reminders/{reminder}/renew', [ReminderController::class, 'renew']);
        Route::post('/reminders/{reminder}/complete', [ReminderController::class, 'complete']);

        Route::get('/notifications', fn (Request $request) => response()->json([
            'data' => ReminderNotification::where('user_id', $request->user()->id)->latest()->paginate(30),
        ]));
        Route::post('/notifications/read-all', function (Request $request) {
            ReminderNotification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

            return response()->json(['message' => 'Notifications marked as read.']);
        });
    });
});
