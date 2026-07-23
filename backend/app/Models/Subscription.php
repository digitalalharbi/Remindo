<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = ['user_id', 'plan_id', 'status', 'trial_ends_at', 'current_period_ends_at', 'cancelled_at'];

    protected function casts(): array
    {
        return ['trial_ends_at' => 'datetime', 'current_period_ends_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
