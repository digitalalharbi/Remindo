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
            'title' => $this->title,
            'description' => $this->description,
            'reference_number' => $this->reference_number,
            'issuer' => $this->issuer,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'issue_date' => $this->issue_date?->toDateString(),
            'status' => $this->status,
            'days_until_expiry' => $this->daysUntilExpiry(),
            'recurrence' => $this->recurrence,
            'recurrence_interval' => $this->recurrence_interval,
            'recurrence_unit' => $this->recurrence_unit,
            'snoozed_until' => $this->snoozed_until?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'document_id' => $this->document_id,
            'notifications' => ReminderNotificationResource::collection($this->whenLoaded('notifications')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
