<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReminderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'expires_at' => $this->expires_at?->toDateString(),
            'remind_days_before' => $this->remind_days_before,
            'channel' => $this->channel,
            'category' => $this->category,
            'notes' => $this->notes,
            'priority' => $this->priority,
            'recurrence' => $this->recurrence,
            'status' => $this->status,
            'snoozed_until' => $this->snoozed_until?->toISOString(),
            'schedules' => $this->whenLoaded('schedules', fn () => $this->schedules->map(fn ($schedule) => [
                'id' => $schedule->id,
                'channel' => $schedule->channel,
                'scheduled_at' => $schedule->scheduled_at->toISOString(),
                'status' => $schedule->status,
                'attempts' => $schedule->attempts,
                'sent_at' => $schedule->sent_at?->toISOString(),
            ])),
            'completed_at' => $this->completed_at?->toISOString(),
            'renewed_at' => $this->renewed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
