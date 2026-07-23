<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MailPreview;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PreviewController extends Controller
{
    /**
     * Public preview status — lets the frontend render the "Preview Mode" badge
     * and surface demo credentials without hard-coding them. Returns harmless,
     * non-secret flags only.
     */
    public function status(): JsonResponse
    {
        return ApiResponse::success([
            'preview' => (bool) config('preview.enabled'),
            'demo_email' => config('preview.enabled') ? config('preview.demo_email') : null,
            'sandbox_integrations' => config('preview.enabled') ? [
                'payments', 'sms', 'whatsapp', 'oauth', 'calendar_sync', 'ai_extraction', 'email_delivery',
            ] : [],
        ]);
    }

    /** Admin mailbox: list captured preview emails, newest first. */
    public function mailLog(): JsonResponse
    {
        $mail = MailPreview::latest()->paginate(30);

        return ApiResponse::success(
            collect($mail->items())->map(fn (MailPreview $m) => [
                'id' => $m->id,
                'to' => $m->to,
                'subject' => $m->subject,
                'from' => $m->from,
                'created_at' => $m->created_at?->toIso8601String(),
            ]),
            meta: [
                'current_page' => $mail->currentPage(),
                'last_page' => $mail->lastPage(),
                'total' => $mail->total(),
            ],
        );
    }

    /** Admin mailbox: full captured email (html/text body). */
    public function mailShow(MailPreview $mailPreview): JsonResponse
    {
        return ApiResponse::success([
            'id' => $mailPreview->id,
            'to' => $mailPreview->to,
            'cc' => $mailPreview->cc,
            'from' => $mailPreview->from,
            'subject' => $mailPreview->subject,
            'html' => $mailPreview->html,
            'text' => $mailPreview->text,
            'created_at' => $mailPreview->created_at?->toIso8601String(),
        ]);
    }
}
