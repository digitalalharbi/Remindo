<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'channels', 'quiet_hours_enabled', 'quiet_start', 'quiet_end', 'fallback_order',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'fallback_order' => 'array',
            'quiet_hours_enabled' => 'boolean',
        ];
    }

    /** Is the given local hour (0-23) inside the quiet window? */
    public function isQuietAt(int $hour): bool
    {
        if (! $this->quiet_hours_enabled || $this->quiet_start === null || $this->quiet_end === null) {
            return false;
        }

        // Handles windows that wrap past midnight (e.g. 22 → 7).
        return $this->quiet_start <= $this->quiet_end
            ? $hour >= $this->quiet_start && $hour < $this->quiet_end
            : $hour >= $this->quiet_start || $hour < $this->quiet_end;
    }

    public function allows(string $channel): bool
    {
        // Default: all channels enabled unless explicitly turned off.
        return $this->channels === null || in_array($channel, $this->channels, true);
    }
}
