<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Public (unauthenticated) marketing-site endpoints. */
class PublicController extends Controller
{
    /** Published FAQs (localized maps) for the public FAQ page + FAQ schema. */
    public function faqs(): JsonResponse
    {
        $faqs = Faq::where('published', true)->orderBy('sort_order')->get(['id', 'question', 'answer', 'category']);

        return ApiResponse::success($faqs);
    }

    /** Contact form submission (throttled, stored for staff follow-up). */
    public function contact(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
            'locale' => ['nullable', 'string', 'max:8'],
        ]);

        DB::table('contact_messages')->insert([
            'id' => (string) Str::uuid7(),
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'locale' => $data['locale'] ?? null,
            'ip_address' => $request->ip(),
            'handled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ApiResponse::message('ok');
    }
}
