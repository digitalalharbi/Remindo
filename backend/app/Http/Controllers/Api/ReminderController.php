<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReminderRequest;
use App\Http\Resources\ReminderResource;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReminderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $reminders = Reminder::query()
            ->when($request->string('status')->isNotEmpty(), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('expires_at')
            ->paginate(20);

        return ReminderResource::collection($reminders);
    }

    public function store(StoreReminderRequest $request): JsonResponse
    {
        $reminder = Reminder::create([
            ...$request->validated(),
            'user_id' => $request->user()?->id,
            'status' => 'active',
        ]);

        return (new ReminderResource($reminder))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Reminder $reminder): ReminderResource
    {
        return new ReminderResource($reminder);
    }

    public function update(StoreReminderRequest $request, Reminder $reminder): ReminderResource
    {
        $reminder->update($request->validated());

        return new ReminderResource($reminder->fresh());
    }

    public function destroy(Reminder $reminder): Response
    {
        $reminder->delete();

        return response()->noContent();
    }
}
