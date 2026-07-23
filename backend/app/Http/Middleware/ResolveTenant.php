<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current organization for the authenticated user and binds it into
 * the Tenancy container so tenant-scoped models are automatically isolated.
 *
 * The active organization is the user's current_organization_id, optionally
 * overridden per-request by an `X-Organization-Id` header (validated against
 * membership).
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $organizationId = $request->header('X-Organization-Id') ?: $user->current_organization_id;

            // Only honor an organization the user actually belongs to.
            if ($organizationId && $user->organizations()->whereKey($organizationId)->exists()) {
                Tenancy::set($organizationId);
            } elseif ($user->current_organization_id) {
                Tenancy::set($user->current_organization_id);
            }
        }

        return $next($request);
    }
}
