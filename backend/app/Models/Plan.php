<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }

    public function isUnlimited(): bool
    {
        return $this->reminder_limit === -1;
    }

    /** Monthly price in a given currency, falling back to the base price. */
    public function monthlyPriceFor(string $currency): int
    {
        return (int) ($this->prices->firstWhere('currency', $currency)?->price_monthly ?? $this->price_monthly);
    }

    public function yearlyPriceFor(string $currency): int
    {
        return (int) ($this->prices->firstWhere('currency', $currency)?->price_yearly ?? $this->price_yearly);
    }
}
