<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /** Resend the verification email to the authenticated user. */
    public function send(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return ApiResponse::message(__('auth.email_verified'));
        }

        $request->user()->sendEmailVerificationNotification();

        return ApiResponse::message(__('auth.verification_sent'));
    }

    /** Verify via the signed link (id + hash). */
    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return ApiResponse::error(__('auth.invalid_token'), 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return ApiResponse::message(__('auth.email_verified'));
    }
}
