<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function log(string $action, ?Model $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'organization_id' => Tenancy::currentId(),
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Platform-level admin action (no tenant). These rows are the immutable
     * super-admin audit trail — every admin mutation calls this.
     */
    public function logAdmin(string $action, ?Model $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'organization_id' => null,
            'user_id' => auth()->id(),
            'action' => 'admin.'.$action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
        ]);
    }
}
