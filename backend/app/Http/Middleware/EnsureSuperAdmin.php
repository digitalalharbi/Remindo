<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Gate for the Super Admin API — only Remindo staff (is_super_admin). */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_super_admin) {
            return ApiResponse::error(__('auth.forbidden'), 403);
        }

        return $next($request);
    }
}
