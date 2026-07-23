<?php

namespace App\Http\Controllers\Api;

use App\Contracts\DocumentExtractor;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\AI\AiUsageTracker;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DocumentController extends Controller
{
    // Accepted document types (MIME allow-list — validated server-side).
    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg', 'image/png', 'image/webp', 'image/heic',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function __construct(
        private readonly DocumentExtractor $extractor,
        private readonly AiUsageTracker $usage,
    ) {}

    /** List the organization's documents (most recent first). */
    public function index(): JsonResponse
    {
        $documents = Document::query()->latest()->limit(100)->get()->map(fn (Document $d) => [
            'id' => $d->id,
            'original_name' => $d->original_name,
            'mime' => $d->mime,
            'size' => $d->size,
            'extraction_status' => $d->extraction_status,
            'created_at' => $d->created_at?->toIso8601String(),
        ]);

        return ApiResponse::success($documents);
    }

    /** Upload a document (drag-and-drop from the client). */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => [
                'required', 'file', 'max:10240', // 10 MB
                'mimetypes:'.implode(',', self::ALLOWED_MIMES),
            ],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/'.Tenancy::currentId(), 'local');

        $document = Document::create([
            'uploaded_by' => auth()->id(),
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'scan_status' => 'clean', // hook a real scanner here in production
        ]);

        return ApiResponse::success([
            'id' => $document->id,
            'original_name' => $document->original_name,
            'mime' => $document->mime,
            'size' => $document->size,
        ], status: 201);
    }

    /**
     * Run AI extraction on an uploaded document. Returns the extracted fields
     * for the user to REVIEW — this never creates a reminder on its own.
     */
    public function extract(Document $document): JsonResponse
    {
        $organization = Tenancy::current();

        if (! $this->usage->canUse($organization)) {
            return ApiResponse::error(__('ai.limit_reached'), 402);
        }

        $document->update(['extraction_status' => 'pending']);

        $result = $this->extractor->extract($document);

        $this->usage->record('extract', $result->provider, $result->tokensUsed);
        $document->update([
            'extraction_status' => 'completed',
            'extracted' => $result->toArray(),
        ]);

        return ApiResponse::success($result->toArray(), __('ai.extracted'));
    }

    /** Parse a natural-language instruction into reminder fields (for review). */
    public function parse(Request $request): JsonResponse
    {
        $request->validate(['text' => ['required', 'string', 'max:500']]);

        $organization = Tenancy::current();
        if (! $this->usage->canUse($organization)) {
            return ApiResponse::error(__('ai.limit_reached'), 402);
        }

        $result = $this->extractor->parseInstruction($request->string('text'));
        $this->usage->record('parse', $result->provider, $result->tokensUsed);

        return ApiResponse::success($result->toArray(), __('ai.extracted'));
    }

    /** Replace the file backing a document (keeps the same document id). */
    public function replace(Request $request, Document $document): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimetypes:'.implode(',', self::ALLOWED_MIMES)],
        ]);

        // Remove the old file, store the new one.
        Storage::disk($document->disk)->delete($document->path);
        $file = $request->file('file');
        $path = $file->store('documents/'.Tenancy::currentId(), 'local');

        $document->update([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extraction_status' => 'none',
            'extracted' => null,
        ]);

        return ApiResponse::success(['id' => $document->id], __('ai.replaced'));
    }

    /** Delete a document and its stored file (tenant-scoped by the model). */
    public function destroy(Document $document): JsonResponse
    {
        Storage::disk($document->disk)->delete($document->path);
        $document->delete();

        return ApiResponse::message('ok');
    }

    /** Issue a short-lived signed URL to download the original file. */
    public function download(Document $document): JsonResponse
    {
        $url = URL::temporarySignedRoute(
            'documents.file',
            now()->addMinutes(5),
            ['document' => $document->id],
        );

        return ApiResponse::success(['url' => $url]);
    }

    /** Stream the original file — authorized by the signed URL, not the session. */
    public function file(Document $document)
    {
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->download(
            $document->path,
            $document->original_name,
        );
    }
}
