<?php

namespace App\Policies;

use App\Models\Reminder;
use App\Models\User;
use App\Support\Tenancy;

class ReminderPolicy
{
    public function viewAny(User $user): bool
    {
        return Tenancy::currentId() !== null;
    }

    public function view(User $user, Reminder $reminder): bool
    {
        return $this->belongsToCurrentTenant($reminder);
    }

    public function create(User $user): bool
    {
        return Tenancy::currentId() !== null;
    }

    public function update(User $user, Reminder $reminder): bool
    {
        return $this->belongsToCurrentTenant($reminder);
    }

    public function delete(User $user, Reminder $reminder): bool
    {
        return $this->belongsToCurrentTenant($reminder);
    }

    /**
     * Defense in depth: even though the global tenant scope already prevents
     * cross-organization row access, the policy re-checks ownership explicitly.
     */
    private function belongsToCurrentTenant(Reminder $reminder): bool
    {
        return $reminder->organization_id === Tenancy::currentId();
    }
}
