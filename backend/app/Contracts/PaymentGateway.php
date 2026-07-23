<?php

namespace App\Contracts;

use App\DTO\ChargeResult;

/**
 * Provider-agnostic payments. Implementations (Moyasar, Tap, Stripe, …) are
 * selected by config('services.payments.provider') and must be swappable without
 * touching billing logic. Card data is never stored by the application.
 */
interface PaymentGateway
{
    /**
     * Charge for a subscription period.
     *
     * @param  int  $amount  amount in minor units
     */
    public function charge(int $amount, string $currency, array $meta = []): ChargeResult;

    public function providerName(): string;
}
