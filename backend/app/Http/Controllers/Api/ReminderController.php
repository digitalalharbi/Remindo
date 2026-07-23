<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reminder\RenewReminderRequest;
use App\Http\Requests\Reminder\StoreReminderRequest;
use App\Http\Requests\Reminder\UpdateReminderRequest;
use App\Http\Resources\ReminderResource;
use App\Models\Reminder;
use App\Services\PlanGate;
use App\Services\ReminderService;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function __construct(
        private readonly ReminderService $reminders,
        private readonly PlanGate $planGate,
    ) {}

    /** List with search, filters, sorting, pagination. */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Reminder::class);

        $query = Reminder::query()->with(['category', 'tags']);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('reference_number', 'ilike', "%{$search}%")
                    ->orWhere('issuer', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('tag_id')) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $request->input('tag_id')));
        }

        if ($request->string('filter')->value() === 'overdue') {
            $query->overdue();
        } elseif ($request->string('filter')->value() === 'upcoming') {
            $query->upcoming((int) $request->integer('days', 30));
        }

        $sort = $request->string('sort')->value() ?: 'expiry_date';
        $direction = $request->string('direction')->value() === 'desc' ? 'desc' : 'asc';
        if (in_array($sort, ['expiry_date', 'created_at', 'title', 'status'])) {
            $query->orderBy($sort, $direction);
        }

        $reminders = $query->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            ReminderResource::collection($reminders),
            meta: [
                'current_page' => $reminders->currentPage(),
                'last_page' => $reminders->lastPage(),
                'per_page' => $reminders->perPage(),
                'total' => $reminders->total(),
            ],
        );
    }

    public function store(StoreReminderRequest $request): JsonResponse
    {
        $this->authorize('create', Reminder::class);

        if (! $this->planGate->canCreateReminder(Tenancy::current())) {
            return ApiResponse::error(__('errors.plan_limit_reached'), 402);
        }

        $reminder = $this->reminders->create($request->validated());

        return ApiResponse::success(
            new ReminderResource($reminder),
            __('reminders.created'),
            status: 201,
        );
    }

    public function show(Reminder $reminder): JsonResponse
    {
        $this->authorize('view', $reminder);

        return ApiResponse::success(
            new ReminderResource($reminder->load(['category', 'tags', 'assignee', 'notifications'])),
        );
    }

    public function update(UpdateReminderRequest $request, Reminder $reminder): JsonResponse
    {
        $this->authorize('update', $reminder);

        $reminder = $this->reminders->update($reminder, $request->validated());

        return ApiResponse::success(new ReminderResource($reminder), __('reminders.updated'));
    }

    public function destroy(Reminder $reminder): JsonResponse
    {
        $this->authorize('delete', $reminder);
        $reminder->delete();

        return ApiResponse::message(__('reminders.deleted'));
    }

    public function complete(Reminder $reminder): JsonResponse
    {
        $this->authorize('update', $reminder);

        return ApiResponse::success(
            new ReminderResource($this->reminders->complete($reminder)),
            __('reminders.completed'),
        );
    }

    public function renew(RenewReminderRequest $request, Reminder $reminder): JsonResponse
    {
        $this->authorize('update', $reminder);

        $reminder = $this->reminders->renew($reminder, $request->string('new_expiry_date'));

        return ApiResponse::success(new ReminderResource($reminder), __('reminders.renewed'));
    }

    public function snooze(Request $request, Reminder $reminder): JsonResponse
    {
        $this->authorize('update', $reminder);
        $request->validate(['until' => ['required', 'date', 'after:now']]);

        return ApiResponse::success(
            new ReminderResource($this->reminders->snooze($reminder, $request->string('until'))),
            __('reminders.snoozed'),
        );
    }

    public function archive(Reminder $reminder): JsonResponse
    {
        $this->authorize('update', $reminder);

        return ApiResponse::success(
            new ReminderResource($this->reminders->archive($reminder)),
            __('reminders.archived'),
        );
    }
}
