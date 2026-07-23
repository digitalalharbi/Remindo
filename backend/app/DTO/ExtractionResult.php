<?php

namespace App\DTO;

/**
 * The structured result of extracting reminder fields from a document.
 * Presented to the user for review — never auto-saved.
 */
class ExtractionResult
{
    /**
     * @param  int[]  $suggestedOffsets  suggested reminder offsets (days before expiry)
     */
    public function __construct(
        public ?string $title = null,
        public ?string $referenceNumber = null,
        public ?string $issuer = null,
        public ?string $issueDate = null,
        public ?string $expiryDate = null,
        public ?string $summary = null,
        public array $suggestedOffsets = [30, 7, 1],
        public float $confidence = 0.0,
        public int $tokensUsed = 0,
        public string $provider = 'mock',
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'reference_number' => $this->referenceNumber,
            'issuer' => $this->issuer,
            'issue_date' => $this->issueDate,
            'expiry_date' => $this->expiryDate,
            'summary' => $this->summary,
            'suggested_offsets' => $this->suggestedOffsets,
            'confidence' => $this->confidence,
            'provider' => $this->provider,
            // Explicit review gate: the client must confirm before creating a reminder.
            'requires_review' => true,
        ];
    }
}
