<?php

use App\Http\Controllers\Api\Admin\AdminAccountController;
use App\Http\Controllers\Api\Admin\AdminContentController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\AdminCouponController;
use App\Http\Controllers\Api\Admin\AdminPlanController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TeamController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ApiResponse::success(['status' => 'ok', 'service' => 'remindo-api']));

// ── Public ────────────────────────────────────────────────
Route::get('/plans', [PlanController::class, 'index']);
Route::get('/faqs', [PublicController::class, 'faqs']);
Route::post('/contact', [PublicController::class, 'contact'])->middleware('throttle:5,1');

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
// logout + me stay reachable even when suspended (so the client can recover).
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

Route::middleware(['auth:sanctum', 'tenant', 'not_suspended'])->group(function () {
    Route::post('/auth/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1');

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Profile, team, notifications
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::get('/team', [TeamController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

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
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::post('/documents/{document}/extract', [DocumentController::class, 'extract']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);
    Route::post('/ai/parse', [DocumentController::class, 'parse']);
});

// ── Super Admin (Remindo staff only) ──────────────────────
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'stats']);
    Route::get('/audit-logs', [AdminController::class, 'auditLogs']);

    // Accounts
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/users/{user}', [AdminAccountController::class, 'showUser']);
    Route::post('/users/{user}/suspend', [AdminAccountController::class, 'suspendUser']);
    Route::post('/users/{user}/reactivate', [AdminAccountController::class, 'reactivateUser']);
    Route::get('/organizations', [AdminController::class, 'organizations']);
    Route::get('/organizations/{organization}', [AdminAccountController::class, 'showOrganization']);
    Route::post('/organizations/{organization}/suspend', [AdminAccountController::class, 'suspendOrganization']);
    Route::post('/organizations/{organization}/reactivate', [AdminAccountController::class, 'reactivateOrganization']);

    // Plans + prices
    Route::get('/plans', [AdminController::class, 'plans']);
    Route::post('/plans', [AdminPlanController::class, 'store']);
    Route::patch('/plans/{plan}', [AdminPlanController::class, 'update']);
    Route::post('/plans/{plan}/toggle', [AdminPlanController::class, 'toggle']);
    Route::post('/plans/reorder', [AdminPlanController::class, 'reorder']);
    Route::delete('/plans/{plan}', [AdminPlanController::class, 'destroy']);

    // Coupons
    Route::get('/coupons', [AdminCouponController::class, 'index']);
    Route::post('/coupons', [AdminCouponController::class, 'store']);
    Route::patch('/coupons/{coupon}', [AdminCouponController::class, 'update']);
    Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy']);

    Route::get('/invoices', [AdminController::class, 'invoices']);

    // Content management
    Route::get('/flags', [AdminContentController::class, 'flags']);
    Route::post('/flags', [AdminContentController::class, 'upsertFlag']);
    Route::get('/languages', [AdminContentController::class, 'languages']);
    Route::post('/languages/{language}/toggle', [AdminContentController::class, 'toggleLanguage']);
    Route::get('/faqs', [AdminContentController::class, 'faqs']);
    Route::post('/faqs', [AdminContentController::class, 'storeFaq']);
    Route::patch('/faqs/{faq}', [AdminContentController::class, 'updateFaq']);
    Route::delete('/faqs/{faq}', [AdminContentController::class, 'destroyFaq']);
    Route::get('/settings', [AdminContentController::class, 'settings']);
    Route::post('/settings', [AdminContentController::class, 'saveSetting']);
});

// Signed file streaming (authorized by the signed URL, not the session).
Route::get('/documents/{document}/file', [DocumentController::class, 'file'])
    ->middleware('signed')->name('documents.file');
