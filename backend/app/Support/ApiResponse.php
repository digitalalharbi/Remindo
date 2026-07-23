<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Unified JSON envelope for every API response.
 *
 *   success → { "data": ..., "message": ..., "meta": ... }
 *   error   → { "message": ..., "errors": ... }
 */
class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, array $meta = [], int $status = 200): JsonResponse
    {
        $payload = ['data' => $data];

        if ($message !== null) {
            $payload['message'] = $message;
        }
        if (! empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    public static function message(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = ['message' => $message];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
