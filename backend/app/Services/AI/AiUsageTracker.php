<?php

namespace App\Services\AI;

use App\Models\Organization;
use App\Support\Tenancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Records AI usage per organization and enforces the monthly operation cap
 * (min of the plan's ai_operations_limit and the global config limit).
 */
class AiUsageTracker
{
    public function operationsThisMonth(string $organizationId): int
    {
        return DB::table('ai_usages')
            ->where('organization_id', $organizationId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }

    public function canUse(Organization $organization): bool
    {
        $planLimit = $organization->plan?->ai_operations_limit ?? 0;

        if ($planLimit <= 0) {
            return false; // plan has no AI allowance
        }

        return $this->operationsThisMonth($organization->id) < $planLimit;
    }

    public function record(string $operation, string $provider, int $tokens = 0, int $costMicros = 0): void
    {
        DB::table('ai_usages')->insert([
            'id' => (string) Str::uuid7(),
            'organization_id' => Tenancy::currentId(),
            'user_id' => auth()->id(),
            'operation' => $operation,
            'provider' => $provider,
            'tokens' => $tokens,
            'cost_micros' => $costMicros,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
