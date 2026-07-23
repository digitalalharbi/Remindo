<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** The user's in-app notification center (most recent first). */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()->limit(30)->get()->map(fn ($n) => [
            'id' => $n->id,
            'data' => $n->data,
            'read' => ! is_null($n->read_at),
            'created_at' => $n->created_at?->toIso8601String(),
        ]);

        return ApiResponse::success($notifications, meta: [
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->where('id', $id)->update(['read_at' => now()]);

        return ApiResponse::message('ok');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return ApiResponse::message('ok');
    }
}
