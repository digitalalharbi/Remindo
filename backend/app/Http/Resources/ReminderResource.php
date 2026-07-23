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
            'status' => $this->status,
            'completed_at' => $this->completed_at?->toISOString(),
            'renewed_at' => $this->renewed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
