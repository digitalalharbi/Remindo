<?php

namespace App\DTO;

class ChargeResult
{
    public function __construct(
        public bool $success,
        public string $reference,
        public string $provider,
        public ?string $failureReason = null,
    ) {}
}
