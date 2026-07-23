<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name, // localized map { en, ar, es, tr }
            'slug' => $this->slug,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_system' => $this->is_system,
        ];
    }
}
