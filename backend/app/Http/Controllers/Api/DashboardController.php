<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->user()->reminders()->whereNotIn('status', ['archived', 'cancelled']);
        $today = now()->startOfDay();

        return response()->json(['data' => [
            'counts' => [
                'active' => (clone $query)->where('status', 'active')->count(),
                'due_7_days' => (clone $query)->whereBetween('expires_at', [$today, $today->copy()->addDays(7)])->count(),
                'due_30_days' => (clone $query)->whereBetween('expires_at', [$today, $today->copy()->addDays(30)])->count(),
                'overdue' => (clone $query)->whereDate('expires_at', '<', $today)->whereNotIn('status', ['completed', 'renewed'])->count(),
            ],
            'upcoming' => (clone $query)->with('schedules')->whereDate('expires_at', '>=', $today)->orderBy('expires_at')->limit(5)->get(),
            'activity' => ReminderNotification::where('user_id', $request->user()->id)->latest()->limit(8)->get(),
            'subscription' => $request->user()->subscriptions()->with('plan')->where('status', 'active')->latest()->first(),
        ]]);
    }
}
