<?php

namespace App\Contracts;

use App\DTO\ExtractionResult;
use App\Models\Document;

/**
 * Provider-agnostic document extraction. Implementations must be replaceable via
 * config (mock, openai, anthropic, …) without changing any calling code.
 */
interface DocumentExtractor
{
    public function extract(Document $document): ExtractionResult;

    /** Parse a natural-language instruction into reminder fields. */
    public function parseInstruction(string $text): ExtractionResult;

    public function providerName(): string;
}
