<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->safe()->except('password_confirmation'));
        $plan = Plan::firstOrCreate(['code' => 'free'], [
            'name' => 'Free', 'price_monthly' => 0, 'currency' => 'SAR',
            'reminder_limit' => 5, 'email_limit' => 25,
        ]);
        $user->subscriptions()->create(['plan_id' => $plan->id, 'status' => 'active']);
        event(new Registered($user));

        return response()->json([
            'data' => ['user' => $user, 'token' => $user->createToken('registration')->plainTextToken],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages(['email' => [__('auth.failed')]]);
        }
        if ($user->disabled_at) {
            throw ValidationException::withMessages(['email' => ['This account is disabled.']]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $user->createToken($request->string('device_name', 'web')->toString())->plainTextToken,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->load('subscriptions.plan')]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function sessions(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->tokens()->latest()->get([
            'id', 'name', 'last_used_at', 'created_at', 'expires_at',
        ])]);
    }

    public function revokeSession(Request $request, int $token): JsonResponse
    {
        $request->user()->tokens()->whereKey($token)->delete();

        return response()->json(['message' => 'Session revoked.']);
    }
}
