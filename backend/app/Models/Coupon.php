<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasUuids;

    protected $fillable = [
        'code', 'type', 'value', 'currency', 'duration',
        'max_redemptions', 'times_redeemed', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function isRedeemable(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->max_redemptions !== null && $this->times_redeemed >= $this->max_redemptions) {
            return false;
        }

        return true;
    }

    /** Apply the discount to an amount (minor units). */
    public function discountFor(int $amount): int
    {
        return $this->type === 'percent'
            ? (int) round($amount * min(100, $this->value) / 100)
            : min($amount, $this->value);
    }
}
