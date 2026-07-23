<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return response()->json(['message' => 'Verification message sent.']);
    }

    public function verify(Request $request, int $id, string $hash): JsonResponse
    {
        abort_unless($request->user()->getKey() === $id, 403);
        abort_unless(hash_equals($hash, sha1($request->user()->getEmailForVerification())), 403);
        $request->user()->markEmailAsVerified();

        return response()->json(['message' => 'Email verified.']);
    }
}
