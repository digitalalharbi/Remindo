<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'key', 'name', 'description', 'price_monthly', 'price_yearly', 'currency',
        'reminder_limit', 'user_limit', 'ai_operations_limit', 'features', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function isUnlimited(): bool
    {
        return $this->reminder_limit === -1;
    }
}
