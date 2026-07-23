<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReminderResource;
use App\Models\ActivityLog;
use App\Models\Reminder;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * The simple dashboard: upcoming, overdue, this week, this month, recent
     * activity. Deliberately not a heavy analytics screen.
     */
    public function index(): JsonResponse
    {
        $overdue = Reminder::overdue()->orderBy('expiry_date')->limit(50)->get();
        $thisWeek = Reminder::upcoming(7)->orderBy('expiry_date')->get();
        $thisMonth = Reminder::upcoming(30)->orderBy('expiry_date')->get();

        $activity = ActivityLog::where('organization_id', Tenancy::currentId())
            ->latest()->limit(10)->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'subject_id' => $log->subject_id,
                'properties' => $log->properties,
                'created_at' => $log->created_at?->toIso8601String(),
            ]);

        return ApiResponse::success([
            'counts' => [
                'active' => Reminder::active()->count(),
                'overdue' => Reminder::overdue()->count(),
                'this_week' => $thisWeek->count(),
                'this_month' => $thisMonth->count(),
            ],
            'overdue' => ReminderResource::collection($overdue),
            'this_week' => ReminderResource::collection($thisWeek),
            'upcoming' => ReminderResource::collection($thisMonth),
            'recent_activity' => $activity,
        ]);
    }
}
