<?php

namespace App\Services\AI;

use App\Contracts\DocumentExtractor;
use App\DTO\ExtractionResult;
use App\Models\Document;
use Carbon\CarbonImmutable;

/**
 * Replaceable sandbox extractor. It performs REAL, deterministic heuristic
 * extraction from the document's filename/metadata and from natural-language
 * text — enough to run the whole "upload → review → save" flow locally without
 * an external AI provider. Swap AI_PROVIDER to use a live provider in production.
 *
 * It never sends data anywhere and never auto-applies its output: the result is
 * returned to the user for review before any reminder is created.
 */
class MockDocumentExtractor implements DocumentExtractor
{
    /** Keyword → [issuer guess, months until expiry, suggested offsets]. */
    private const TYPES = [
        'insurance' => ['months' => 12, 'offsets' => [30, 7, 1]],
        'تأمين' => ['months' => 12, 'offsets' => [30, 7, 1]],
        'license' => ['months' => 12, 'offsets' => [45, 14, 1]],
        'ترخيص' => ['months' => 12, 'offsets' => [45, 14, 1]],
        'contract' => ['months' => 12, 'offsets' => [60, 30, 7]],
        'عقد' => ['months' => 12, 'offsets' => [60, 30, 7]],
        'passport' => ['months' => 120, 'offsets' => [180, 90, 30]],
        'جواز' => ['months' => 120, 'offsets' => [180, 90, 30]],
        'subscription' => ['months' => 1, 'offsets' => [7, 1]],
        'اشتراك' => ['months' => 1, 'offsets' => [7, 1]],
        'lease' => ['months' => 12, 'offsets' => [60, 30, 7]],
        'إيجار' => ['months' => 12, 'offsets' => [60, 30, 7]],
    ];

    public function extract(Document $document): ExtractionResult
    {
        $name = pathinfo($document->original_name, PATHINFO_FILENAME);
        $title = trim(preg_replace('/[_-]+/', ' ', $name)) ?: $name;
        $haystack = mb_strtolower($document->original_name);

        $type = $this->matchType($haystack);
        $issue = CarbonImmutable::now()->subMonths((int) ($type['months'] / 2));
        $expiry = $issue->addMonths($type['months']);

        return new ExtractionResult(
            title: mb_convert_case($title, MB_CASE_TITLE),
            referenceNumber: 'REF-'.strtoupper(substr(md5($document->id), 0, 8)),
            issuer: null,
            issueDate: $issue->toDateString(),
            expiryDate: $expiry->toDateString(),
            summary: 'Detected an expiry-bearing document. Please review the extracted dates before saving.',
            suggestedOffsets: $type['offsets'],
            confidence: 0.62,
            tokensUsed: 0,
            provider: $this->providerName(),
        );
    }

    /**
     * Parse phrases like:
     *   "ذكّرني بتجديد التأمين قبل شهر وأسبوع"
     *   "remind me to renew the insurance 1 month and 1 week before"
     */
    public function parseInstruction(string $text): ExtractionResult
    {
        $lower = mb_strtolower($text);
        $offsets = [];

        // month(s) / شهر
        if (preg_match('/(\d+)?\s*(month|شهر|أشهر|شهور)/u', $lower, $m)) {
            $offsets[] = 30 * max(1, (int) ($m[1] ?? 1));
        }
        // week(s) / أسبوع
        if (preg_match('/(\d+)?\s*(week|أسبوع|اسبوع|أسابيع)/u', $lower, $m)) {
            $offsets[] = 7 * max(1, (int) ($m[1] ?? 1));
        }
        // day(s) / يوم
        if (preg_match('/(\d+)\s*(day|يوم|أيام|ايام)/u', $lower, $m)) {
            $offsets[] = max(1, (int) $m[1]);
        }

        $offsets = array_values(array_unique($offsets)) ?: [7, 1];
        rsort($offsets);

        // Title: strip common lead-ins in both languages.
        $title = trim(preg_replace(
            '/^(remind me( to)?|ذكّرني( بـ| ب)?|ذكرني( بـ| ب)?)\s*/iu',
            '',
            $text,
        ));
        // Drop the trailing "N before" clause from the title.
        $title = trim(preg_replace('/\s*(قبل|before).*/iu', '', $title)) ?: $title;

        return new ExtractionResult(
            title: $title,
            suggestedOffsets: $offsets,
            confidence: 0.7,
            provider: $this->providerName(),
        );
    }

    public function providerName(): string
    {
        return 'mock';
    }

    private function matchType(string $haystack): array
    {
        foreach (self::TYPES as $keyword => $meta) {
            if (str_contains($haystack, $keyword)) {
                return $meta;
            }
        }

        return ['months' => 12, 'offsets' => [30, 7, 1]];
    }
}
