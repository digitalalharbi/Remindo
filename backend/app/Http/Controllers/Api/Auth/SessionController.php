<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Active-session management for database-backed sessions. Lets a user see where
 * they're signed in and revoke individual or all other sessions.
 */
class SessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (config('session.driver') !== 'database') {
            return ApiResponse::success([]);
        }

        $currentId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($s) use ($currentId) {
                return [
                    'id' => $s->id,
                    'ip_address' => $s->ip_address,
                    'user_agent' => $this->describeAgent($s->user_agent),
                    'last_active' => $s->last_activity,
                    'current' => $s->id === $currentId,
                ];
            });

        return ApiResponse::success($sessions);
    }

    /** Revoke a single other session. */
    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($id === $request->session()->getId()) {
            return ApiResponse::error(__('auth.cannot_revoke_current'), 422);
        }

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        return ApiResponse::message(__('auth.session_revoked'));
    }

    /** Revoke every session except the current one. */
    public function destroyOthers(Request $request): JsonResponse
    {
        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return ApiResponse::message(__('auth.other_sessions_revoked'));
    }

    /** Lightweight OS + browser description from the user-agent string. */
    private function describeAgent(?string $ua): string
    {
        if (! $ua) {
            return 'Unknown device';
        }

        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'iPhone'), str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown OS',
        };

        $browser = match (true) {
            str_contains($ua, 'Edg') => 'Edge',
            str_contains($ua, 'Chrome') => 'Chrome',
            str_contains($ua, 'Safari') => 'Safari',
            str_contains($ua, 'Firefox') => 'Firefox',
            default => Str::limit($ua, 20),
        };

        return "{$os} · {$browser}";
    }
}
