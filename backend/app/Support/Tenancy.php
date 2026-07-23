<?php

namespace App\Support;

use App\Models\Organization;

/**
 * Holds the current tenant (organization) for the lifetime of a request or job.
 * Set by middleware from the authenticated user's current organization; read by
 * the BelongsToOrganization global scope.
 */
class Tenancy
{
    protected static ?string $organizationId = null;

    public static function set(?string $organizationId): void
    {
        static::$organizationId = $organizationId;
    }

    public static function currentId(): ?string
    {
        return static::$organizationId;
    }

    public static function current(): ?Organization
    {
        return static::$organizationId ? Organization::find(static::$organizationId) : null;
    }

    public static function clear(): void
    {
        static::$organizationId = null;
    }

    /** Run a callback scoped to a specific organization (used by jobs/scheduler). */
    public static function forOrganization(?string $organizationId, callable $callback): mixed
    {
        $previous = static::$organizationId;
        static::$organizationId = $organizationId;

        try {
            return $callback();
        } finally {
            static::$organizationId = $previous;
        }
    }
}
