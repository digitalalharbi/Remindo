<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarConnection extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'organization_id', 'provider',
        'access_token', 'refresh_token', 'expires_at', 'calendar_id', 'sync_enabled',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected function casts(): array
    {
        return [
            // Provider tokens are encrypted at rest.
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'datetime',
            'sync_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tokenExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
