<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\NewLoginNotification;
use App\Services\OrganizationService;
use App\Support\ApiResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function __construct(private readonly OrganizationService $organizations) {}

    /** Register + create a personal organization + start the session. */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => $request->string('password'),
            'locale' => $request->input('locale', 'en'),
            'country' => $request->input('country'),
            'timezone' => $request->input('timezone', 'UTC'),
        ]);

        $this->organizations->createPersonalOrganizationFor($user);

        event(new Registered($user));

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return ApiResponse::success(
            new UserResource($user->load(['organizations.plan'])),
            __('auth.registered'),
            status: 201,
        );
    }

    /** Cookie-based (SPA) login. */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password'), (string) $user->password)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        if ($user->isSuspended()) {
            throw ValidationException::withMessages(['email' => __('admin.account_suspended_notice')]);
        }

        // If 2FA is enabled, defer login until the code is verified.
        if ($user->hasTwoFactorEnabled()) {
            if ($request->hasSession()) {
                $request->session()->put('2fa:user:id', $user->id);
                $request->session()->put('2fa:remember', $request->boolean('remember'));
            }

            return ApiResponse::success(['two_factor' => true], __('auth.two_factor_required'));
        }

        $this->completeLogin($request, $user, $request->boolean('remember'));

        return ApiResponse::success(
            new UserResource($user->load(['organizations.plan'])),
            __('auth.logged_in'),
        );
    }

    /** Second step of login for 2FA-enabled accounts. */
    public function twoFactorChallenge(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required_without:recovery_code', 'nullable', 'string'],
            'recovery_code' => ['required_without:code', 'nullable', 'string'],
        ]);

        $userId = $request->session()->get('2fa:user:id');
        if (! $userId) {
            return ApiResponse::error(__('auth.two_factor_expired'), 419);
        }

        $user = User::findOrFail($userId);
        $g2fa = new Google2FA;

        $ok = false;
        if ($request->filled('code')) {
            $ok = (bool) $g2fa->verifyKey((string) $user->two_factor_secret, $request->string('code'));
        } elseif ($request->filled('recovery_code')) {
            $codes = $user->two_factor_recovery_codes ?? [];
            if (in_array($request->string('recovery_code')->value(), $codes, true)) {
                $ok = true;
                // Recovery codes are single-use.
                $user->forceFill([
                    'two_factor_recovery_codes' => array_values(array_diff($codes, [$request->string('recovery_code')->value()])),
                ])->save();
            }
        }

        if (! $ok) {
            return ApiResponse::error(__('auth.two_factor_invalid'), 422);
        }

        $remember = (bool) $request->session()->pull('2fa:remember', false);
        $request->session()->forget('2fa:user:id');
        $this->completeLogin($request, $user, $remember);

        return ApiResponse::success(
            new UserResource($user->load(['organizations.plan'])),
            __('auth.logged_in'),
        );
    }

    private function completeLogin(Request $request, User $user, bool $remember): void
    {
        Auth::login($user, $remember);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        // Notify the user of the new sign-in (mail + in-app).
        $user->notify(new NewLoginNotification(
            (string) $request->ip(),
            (string) $request->userAgent(),
        ));
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return ApiResponse::message(__('auth.logged_out'));
    }

    /** Current authenticated user. */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($request->user()->load(['organizations.plan'])),
        );
    }
}
