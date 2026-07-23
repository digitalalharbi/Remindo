<?php

namespace App\Services;

use App\Models\Organization;

/**
 * Enforces plan limits. All limits are stored on the plan and editable from the
 * Super Admin panel, so this reads them dynamically rather than hard-coding.
 */
class PlanGate
{
    public function canCreateReminder(Organization $organization): bool
    {
        $limit = $organization->plan?->reminder_limit ?? 5;

        if ($limit === -1) {
            return true; // unlimited
        }

        return $organization->activeRemindersCount() < $limit;
    }

    public function reminderLimit(Organization $organization): int
    {
        return $organization->plan?->reminder_limit ?? 5;
    }
}
