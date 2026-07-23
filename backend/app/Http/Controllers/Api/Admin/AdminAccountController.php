<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Reminder;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAccountController extends Controller
{
    public function __construct(private readonly ActivityLogger $audit) {}

    public function showUser(User $user): JsonResponse
    {
        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'locale' => $user->locale,
            'country' => $user->country,
            'is_super_admin' => (bool) $user->is_super_admin,
            'suspended' => $user->isSuspended(),
            'suspended_reason' => $user->suspended_reason,
            'two_factor' => $user->hasTwoFactorEnabled(),
            'organizations' => $user->organizations()->get()->map(fn ($o) => [
                'id' => $o->id, 'name' => $o->name, 'role' => $o->pivot->role,
            ]),
            'created_at' => $user->created_at?->toIso8601String(),
        ]);
    }

    public function suspendUser(Request $request, User $user): JsonResponse
    {
        abort_if($user->is_super_admin, 403); // never suspend staff via this endpoint
        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:255']])['reason'] ?? null;

        $user->update(['suspended_at' => now(), 'suspended_reason' => $reason]);
        $user->tokens()->delete();
        $this->audit->logAdmin('user.suspended', $user, ['email' => $user->email, 'reason' => $reason]);

        return ApiResponse::message(__('admin.account_suspended'));
    }

    public function reactivateUser(User $user): JsonResponse
    {
        $user->update(['suspended_at' => null, 'suspended_reason' => null]);
        $this->audit->logAdmin('user.reactivated', $user, ['email' => $user->email]);

        return ApiResponse::message(__('admin.account_reactivated'));
    }

    public function showOrganization(Organization $organization): JsonResponse
    {
        return ApiResponse::success([
            'id' => $organization->id,
            'name' => $organization->name,
            'type' => $organization->type,
            'currency' => $organization->currency,
            'plan' => $organization->plan?->key,
            'members' => $organization->members()->count(),
            'reminders' => Reminder::withoutGlobalScope('organization')
                ->where('organization_id', $organization->id)->count(),
            'suspended' => $organization->isSuspended(),
            'created_at' => $organization->created_at?->toIso8601String(),
        ]);
    }

    public function suspendOrganization(Request $request, Organization $organization): JsonResponse
    {
        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:255']])['reason'] ?? null;
        $organization->update(['suspended_at' => now(), 'suspended_reason' => $reason]);
        $this->audit->logAdmin('organization.suspended', $organization, ['name' => $organization->name]);

        return ApiResponse::message(__('admin.account_suspended'));
    }

    public function reactivateOrganization(Organization $organization): JsonResponse
    {
        $organization->update(['suspended_at' => null, 'suspended_reason' => null]);
        $this->audit->logAdmin('organization.reactivated', $organization, ['name' => $organization->name]);

        return ApiResponse::message(__('admin.account_reactivated'));
    }
}
