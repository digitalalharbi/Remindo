<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks suspended users and organizations from the authenticated API. Super
 * admins are exempt so staff can still manage the platform.
 */
class EnsureNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_super_admin) {
            return $next($request);
        }

        if ($user->isSuspended()) {
            Auth::guard('web')->logout();

            return ApiResponse::error(__('admin.account_suspended_notice'), 403);
        }

        $org = Tenancy::current();
        if ($org && $org->isSuspended()) {
            return ApiResponse::error(__('admin.organization_suspended_notice'), 403);
        }

        return $next($request);
    }
}
