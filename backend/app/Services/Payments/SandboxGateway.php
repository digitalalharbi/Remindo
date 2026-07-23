<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\DTO\ChargeResult;
use Illuminate\Support\Str;

/**
 * Replaceable sandbox gateway. Simulates a successful charge so the full
 * subscribe → invoice flow works end-to-end without real payment credentials.
 * Swap PAYMENTS_PROVIDER (and wire a real gateway in AppServiceProvider) for
 * production — this is NOT a live processor and never touches card data.
 */
class SandboxGateway implements PaymentGateway
{
    public function charge(int $amount, string $currency, array $meta = []): ChargeResult
    {
        // A free ($0) charge always "succeeds" without a processor round-trip.
        return new ChargeResult(
            success: true,
            reference: 'sandbox_'.Str::lower(Str::random(20)),
            provider: $this->providerName(),
        );
    }

    public function providerName(): string
    {
        return 'sandbox';
    }
}
