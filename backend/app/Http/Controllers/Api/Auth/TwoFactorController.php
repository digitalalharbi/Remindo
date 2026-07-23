<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP two-factor authentication (RFC 6238). No external provider required —
 * works with any authenticator app. Secrets/recovery codes are encrypted at rest.
 */
class TwoFactorController extends Controller
{
    private Google2FA $g2fa;

    public function __construct()
    {
        $this->g2fa = new Google2FA;
    }

    /** Begin enrollment: generate a secret and a QR code for the user to scan. */
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();
        $secret = $this->g2fa->generateSecretKey();

        // Store the (encrypted) secret but don't confirm until the user verifies.
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => null])->save();

        $uri = $this->g2fa->getQRCodeUrl('Remindo', $user->email, $secret);

        return ApiResponse::success([
            'secret' => $secret,
            'otpauth_url' => $uri,
            'qr_svg' => $this->qrSvg($uri),
        ]);
    }

    /** Confirm enrollment by verifying a code; returns one-time recovery codes. */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);
        $user = $request->user();

        if (! $user->two_factor_secret || ! $this->g2fa->verifyKey($user->two_factor_secret, $request->string('code'))) {
            return ApiResponse::error(__('auth.two_factor_invalid'), 422);
        }

        $recovery = collect(range(1, 8))->map(fn () => Str::random(10).'-'.Str::random(10))->all();

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recovery,
        ])->save();

        return ApiResponse::success(['recovery_codes' => $recovery], __('auth.two_factor_enabled'));
    }

    public function disable(Request $request): JsonResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return ApiResponse::message(__('auth.two_factor_disabled'));
    }

    public function recoveryCodes(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 400);

        return ApiResponse::success(['recovery_codes' => $user->two_factor_recovery_codes ?? []]);
    }

    private function qrSvg(string $uri): string
    {
        $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd);
        $svg = (new Writer($renderer))->writeString($uri);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
