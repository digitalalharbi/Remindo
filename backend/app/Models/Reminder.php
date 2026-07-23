<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'expires_at',
        'remind_days_before',
        'channel',
        'category',
        'notes',
        'priority',
        'recurrence',
        'snoozed_until',
        'status',
        'completed_at',
        'renewed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'completed_at' => 'datetime',
            'renewed_at' => 'datetime',
            'snoozed_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ReminderSchedule::class);
    }
}
