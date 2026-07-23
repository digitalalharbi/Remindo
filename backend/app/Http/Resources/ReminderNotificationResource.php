<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReminderNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'offset_days' => $this->offset_days,
            'channel' => $this->channel,
            'send_at' => $this->send_at?->toIso8601String(),
            'status' => $this->status,
            'sent_at' => $this->sent_at?->toIso8601String(),
        ];
    }
}
