<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Earning;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class BalanceService
{
    /**
     * Public alias — keeps call sites that use getBalance() working.
     */
    public function getBalance(User|string $user, Asset|string $asset): string
    {
        return $this->for($user, $asset);
    }

    /**
     * Return the user's current quantity of an asset.
     *
     * Formula (all in asset units, not USD):
     *
     *   balance = SUM(credit quantities, status ∈ BALANCE_STATUSES)
     *           − SUM(debit  quantities, status ∈ BALANCE_STATUSES)
     *           − SUM(pending withdrawal quantities)   ← locks funds immediately
     *           + SUM(processed earning amounts)       ← earnings stored as asset units
     *
     * Key design decision:
     *   We SUM the `quantity` column, not `amount`.
     *   `quantity` is the number of asset units and never changes after insert.
     *   `amount` is the USD cost at trade time and is irrelevant to balance math.
     *   `current_price` is NOT used here — price changes do not affect quantity.
     *
     * Current market value is calculated OUTSIDE this method:
     *   value = balance × asset->current_price
     */
    public function for(User|string $user, Asset|string $asset): string
    {
        $userId  = $user instanceof User ? $user->id : $user;
        $assetId = $asset instanceof Asset ? $asset->id : $asset;

        // ── Credits ──────────────────────────────────────────────────────────
        // All approved/processing credit rows for this user + asset.
        $credits = (string) Transaction::query()
            ->where('user_id', $userId)
            ->where('asset_id', $assetId)
            ->where('direction', Transaction::DIRECTION_CREDIT)
            ->whereIn('status', Transaction::BALANCE_STATUSES)
            ->sum('quantity');  // ← quantity, not amount

        // ── Debits ───────────────────────────────────────────────────────────
        // All approved/processing debit rows, PLUS pending withdrawals.
        // Pending withdrawals are included so the user cannot spend funds
        // that are already queued for withdrawal.
        $debits = (string) Transaction::query()
            ->where('user_id', $userId)
            ->where('asset_id', $assetId)
            ->where('direction', Transaction::DIRECTION_DEBIT)
            ->where(function ($query) {
                $query
                    ->whereIn('status', Transaction::BALANCE_STATUSES)
                    ->orWhere(function ($query) {
                        $query
                            ->where('type', Transaction::TYPE_WITHDRAWAL)
                            ->where('status', Transaction::STATUS_PENDING);
                    });
            })
            ->sum('quantity');  // ← quantity, not amount

        // ── Earnings ─────────────────────────────────────────────────────────
        // EarningService stores earnings.amount as asset quantity units
        // (e.g. 0.05 SHELL), calculated as: user_balance × (percentage / 100).
        // We sum them directly — no conversion needed.
        $earnings = (string) Earning::query()
            ->where('user_id', $userId)
            ->where('asset_id', $assetId)
            ->where('status', Earning::STATUS_PROCESSED)
            ->sum('amount');  // earnings.amount IS already in asset units

        // ── Final balance ─────────────────────────────────────────────────────
        // credits − debits + earnings, all in asset units.
        // Result is always >= 0 in practice (balance checks prevent over-spending),
        // but we do not clamp here — a negative result signals a data integrity issue
        // and should be visible rather than silently zeroed.
        return bcadd(
            bcsub($credits, $debits, 8),
            $earnings,
            8
        );
    }

    /**
     * Public alias for getAllBalances().
     */
    public function allFor(User|string $user): Collection
    {
        return $this->getAllBalances($user);
    }

    /**
     * Return all assets where the user has a positive balance,
     * along with current market value.
     *
     * quantity → from ledger, price-independent, never changes with price
     * value    → quantity × current_price, changes with price (correct behaviour)
     */
    public function getAllBalances(User|string $user): Collection
    {
        $userId = $user instanceof User ? $user->id : $user;

        return Asset::where('status', Asset::STATUS_ACTIVE)
            ->orderBy('symbol')
            ->get()
            ->map(function (Asset $asset) use ($userId) {
                $quantity = $this->for($userId, $asset);

                return [
                    'asset'    => $asset,
                    'quantity' => $quantity,
                    // Market value: calculated fresh using the live price.
                    // This is the ONLY place current_price enters balance logic —
                    // and it is purely for display, not for deriving quantity.
                    'value'    => bcmul($quantity, (string) $asset->current_price, 8),
                ];
            })
            ->filter(fn (array $row) => bccomp($row['quantity'], '0', 8) > 0)
            ->values();
    }
}