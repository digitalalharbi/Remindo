<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReminderRequest;
use App\Http\Resources\ReminderResource;
use App\Models\Reminder;
use App\Services\ReminderService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReminderController extends Controller
{
    public function __construct(private readonly ReminderService $reminders) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $reminders = $request->user()->reminders()
            ->with('schedules')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy($request->string('sort', 'expires_at')->toString() === 'created_at' ? 'created_at' : 'expires_at')
            ->paginate(min($request->integer('per_page', 20), 100));

        return ReminderResource::collection($reminders);
    }

    public function store(StoreReminderRequest $request): JsonResponse
    {
        $reminder = $this->reminders->create($request->user(), $request->validated());

        return (new ReminderResource($reminder))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Reminder $reminder): ReminderResource
    {
        $this->authorize('view', $reminder);

        return new ReminderResource($reminder->load('schedules'));
    }

    public function update(StoreReminderRequest $request, Reminder $reminder): ReminderResource
    {
        $this->authorize('update', $reminder);
        $data = $request->validated();
        unset($data['schedules']);
        $reminder->update($data);

        return new ReminderResource($reminder->fresh('schedules'));
    }

    public function destroy(Request $request, Reminder $reminder): Response
    {
        $this->authorize('delete', $reminder);
        $reminder->delete();

        return response()->noContent();
    }

    public function archive(Request $request, Reminder $reminder): ReminderResource
    {
        $this->authorize('update', $reminder);
        $reminder->update(['status' => 'archived']);
        $reminder->delete();

        return new ReminderResource($reminder);
    }

    public function snooze(Request $request, Reminder $reminder): ReminderResource
    {
        $this->authorize('update', $reminder);
        $data = $request->validate(['until' => ['required', 'date', 'after:now']]);

        return new ReminderResource($this->reminders->snooze($reminder, CarbonImmutable::parse($data['until'])));
    }

    public function renew(Request $request, Reminder $reminder): ReminderResource
    {
        $this->authorize('update', $reminder);
        $data = $request->validate(['expires_at' => ['required', 'date', 'after:today']]);

        return new ReminderResource($this->reminders->renew($reminder, $data['expires_at']));
    }

    public function complete(Request $request, Reminder $reminder): ReminderResource
    {
        $this->authorize('update', $reminder);
        $reminder->update(['status' => 'completed', 'completed_at' => now()]);
        $reminder->schedules()->whereIn('status', ['pending', 'queued'])->update(['status' => 'cancelled']);

        return new ReminderResource($reminder->fresh('schedules'));
    }
}
