<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'role' => $this->whenPivotLoaded('organization_user', fn () => $this->pivot->role),
            'plan' => new PlanResource($this->whenLoaded('plan')),
        ];
    }
}
