<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\OrganizationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Social login (Google, Microsoft). The architecture is complete and works once
 * client credentials are supplied; until then each provider stays in a safe
 * disabled state (configured() returns false, the buttons are hidden, and the
 * redirect endpoint returns 422). We never claim a provider works in production
 * before its real secret exists.
 */
class OAuthController extends Controller
{
    private const PROVIDERS = ['google', 'microsoft'];

    public function __construct(private readonly OrganizationService $organizations) {}

    /** Which providers are enabled + configured — drives the login buttons. */
    public function status(): JsonResponse
    {
        return ApiResponse::success([
            'google' => $this->configured('google'),
            'microsoft' => $this->configured('microsoft'),
        ]);
    }

    public function redirect(string $provider): RedirectResponse|JsonResponse
    {
        if (! $this->configured($provider)) {
            return ApiResponse::error(__('auth.oauth_unavailable'), 422);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $frontend = rtrim((string) config('app.frontend_url'), '/');

        if (! $this->configured($provider)) {
            return redirect("{$frontend}/login?error=oauth_unavailable");
        }

        try {
            $oauthUser = Socialite::driver($provider)->user();
        } catch (Throwable) {
            return redirect("{$frontend}/login?error=oauth_failed");
        }

        $user = $this->resolveUser($provider, $oauthUser);
        Auth::login($user, true);
        request()->session()?->regenerate();

        return redirect("{$frontend}/dashboard");
    }

    /**
     * Resolve or create the user, preventing duplicate accounts:
     *  1. Known social account → sign in.
     *  2. Email already registered → LINK this provider to that account.
     *  3. Otherwise → create a new account + personal organization.
     */
    private function resolveUser(string $provider, $oauthUser): User
    {
        $existing = SocialAccount::where('provider', $provider)
            ->where('provider_user_id', $oauthUser->getId())
            ->first();

        if ($existing) {
            return $existing->user;
        }

        $email = $oauthUser->getEmail();
        $user = $email ? User::where('email', $email)->first() : null;

        if (! $user) {
            $user = User::create([
                'name' => $oauthUser->getName() ?: ($email ? explode('@', $email)[0] : 'User'),
                'email' => $email,
                'password' => null, // OAuth-only account
                'email_verified_at' => now(),
                'locale' => 'en',
                'timezone' => 'UTC',
            ]);
            $this->organizations->createPersonalOrganizationFor($user);
        }

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_user_id' => $oauthUser->getId(),
            'email' => $email,
        ]);

        return $user;
    }

    private function configured(string $provider): bool
    {
        if (! in_array($provider, self::PROVIDERS, true)) {
            return false;
        }

        $hasSecret = config("services.{$provider}.client_id") && config("services.{$provider}.client_secret");
        $flagEnabled = FeatureFlag::where('key', "oauth_{$provider}")->value('enabled') ?? false;

        return (bool) $hasSecret && (bool) $flagEnabled;
    }
}
