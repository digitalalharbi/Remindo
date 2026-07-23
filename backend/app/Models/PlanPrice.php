<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanPrice extends Model
{
    use HasUuids;

    protected $fillable = ['plan_id', 'currency', 'price_monthly', 'price_yearly'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
