<?php

namespace App\Services\Notifications;

use App\Models\CreditWallet;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

/**
 * Paid-credit accounting for metered channels (SMS, WhatsApp, AI). Sending is
 * blocked when the balance is insufficient — no negative balances, ever.
 */
class CreditService
{
    public function wallet(Organization $organization, string $channel): CreditWallet
    {
        return CreditWallet::firstOrCreate(
            ['organization_id' => $organization->id, 'channel' => $channel],
            ['balance' => 0],
        );
    }

    public function balance(Organization $organization, string $channel): int
    {
        return (int) $this->wallet($organization, $channel)->balance;
    }

    public function canSend(Organization $organization, string $channel, int $cost = 1): bool
    {
        return $this->balance($organization, $channel) >= $cost;
    }

    /** Atomically deduct credits; returns false if the balance is insufficient. */
    public function deduct(Organization $organization, string $channel, int $cost = 1, ?string $reference = null): bool
    {
        return DB::transaction(function () use ($organization, $channel, $cost, $reference) {
            $wallet = CreditWallet::where('organization_id', $organization->id)
                ->where('channel', $channel)
                ->lockForUpdate()
                ->first();

            if (! $wallet || $wallet->balance < $cost) {
                return false;
            }

            $wallet->decrement('balance', $cost);
            $wallet->transactions()->create([
                'delta' => -$cost,
                'reason' => 'usage',
                'reference_id' => $reference,
            ]);

            return true;
        });
    }

    public function add(Organization $organization, string $channel, int $credits, string $reason = 'purchase', ?string $reference = null): void
    {
        $wallet = $this->wallet($organization, $channel);
        $wallet->increment('balance', $credits);
        $wallet->transactions()->create([
            'delta' => $credits,
            'reason' => $reason,
            'reference_id' => $reference,
        ]);
    }
}
