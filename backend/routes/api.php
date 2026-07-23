<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TagController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ApiResponse::success(['status' => 'ok', 'service' => 'remindo-api']));

// ── Public ────────────────────────────────────────────────
Route::get('/plans', [PlanController::class, 'index']);

// ── Auth (guest) ──────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:6,1');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
});

// ── Authenticated ─────────────────────────────────────────
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1');

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Reminders
    Route::apiResource('reminders', ReminderController::class);
    Route::post('/reminders/{reminder}/complete', [ReminderController::class, 'complete']);
    Route::post('/reminders/{reminder}/renew', [ReminderController::class, 'renew']);
    Route::post('/reminders/{reminder}/snooze', [ReminderController::class, 'snooze']);
    Route::post('/reminders/{reminder}/archive', [ReminderController::class, 'archive']);

    // Taxonomy
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);
    Route::delete('/tags/{tag}', [TagController::class, 'destroy']);

    // Billing (sandbox gateway by default)
    Route::get('/subscription', [SubscriptionController::class, 'show']);
    Route::post('/subscription', [SubscriptionController::class, 'subscribe']);
    Route::delete('/subscription', [SubscriptionController::class, 'cancel']);

    // Documents + AI extraction (review-before-save; never auto-applied)
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::post('/documents/{document}/extract', [DocumentController::class, 'extract']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);
    Route::post('/ai/parse', [DocumentController::class, 'parse']);
});

// Signed file streaming (authorized by the signed URL, not the session).
Route::get('/documents/{document}/file', [DocumentController::class, 'file'])
    ->middleware('signed')->name('documents.file');
