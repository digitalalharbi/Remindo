<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /** Update the authenticated user's profile (name, language, timezone, …). */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'locale' => ['sometimes', Rule::in(['ar', 'en', 'es', 'tr'])],
            'country' => ['sometimes', 'nullable', 'string', 'size:2'],
            'timezone' => ['sometimes', 'string', 'max:64'],
        ]);

        $user = $request->user();
        $user->update($data);

        return ApiResponse::success(
            new UserResource($user->fresh(['organizations.plan'])),
            __('profile.updated'),
        );
    }
}
