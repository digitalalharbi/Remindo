<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'locale' => $this->locale,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'email_verified' => ! is_null($this->email_verified_at),
            'two_factor_enabled' => $this->hasTwoFactorEnabled(),
            'is_super_admin' => (bool) $this->is_super_admin,
            'current_organization_id' => $this->current_organization_id,
            'organizations' => OrganizationResource::collection($this->whenLoaded('organizations')),
        ];
    }
}
