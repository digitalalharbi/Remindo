<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    /** Members of the current organization. */
    public function index(): JsonResponse
    {
        $organization = Tenancy::current();

        $members = $organization->members()->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->pivot->role,
        ]);

        return ApiResponse::success($members, meta: [
            'user_limit' => $organization->plan?->user_limit ?? 1,
        ]);
    }
}
