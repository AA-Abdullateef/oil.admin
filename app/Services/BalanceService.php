<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Earning;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class BalanceService
{
    public function getBalance(User|string $user, Asset|string $asset): string
    {
        return $this->for($user, $asset);
    }

    public function for(User|string $user, Asset|string $asset): string
    {
        $userId = $user instanceof User ? $user->id : $user;
        $assetId = $asset instanceof Asset ? $asset->id : $asset;
        $assetModel = $asset instanceof Asset ? $asset : Asset::find($assetId);
        $rate = (string) ($assetModel?->current_price ?: 0);

        if (bccomp($rate, '0', 8) <= 0) {
            return '0.00000000';
        }

        $credits = (string) Transaction::query()
            ->where('user_id', $userId)
            ->where('asset_id', $assetId)
            ->where('direction', Transaction::DIRECTION_CREDIT)
            ->whereIn('status', Transaction::BALANCE_STATUSES)
            ->sum('amount');

        $debits = (string) Transaction::query()
            ->where('user_id', $userId)
            ->where('asset_id', $assetId)
            ->where('direction', Transaction::DIRECTION_DEBIT)
            ->where(function ($query) {
                $query->whereIn('status', Transaction::BALANCE_STATUSES)
                    ->orWhere(function ($query) {
                        $query->where('type', Transaction::TYPE_WITHDRAWAL)
                            ->where('status', Transaction::STATUS_PENDING);
                    });
            })
            ->sum('amount');

        $earnings = (string) Earning::query()
            ->where('user_id', $userId)
            ->where('asset_id', $assetId)
            ->where('status', Earning::STATUS_PROCESSED)
            ->sum('amount');

        $netAmount = bcadd(bcsub($credits, $debits, 8), bcmul($earnings, $rate, 8), 8);

        return bcdiv($netAmount, $rate, 8);
    }

    public function allFor(User|string $user): Collection
    {
        return $this->getAllBalances($user);
    }

    public function getAllBalances(User|string $user): Collection
    {
        $userId = $user instanceof User ? $user->id : $user;

        return Asset::where('status', Asset::STATUS_ACTIVE)
            ->orderBy('symbol')
            ->get()
            ->map(function (Asset $asset) use ($userId) {
                $quantity = $this->for($userId, $asset);

                return [
                    'asset' => $asset,
                    'quantity' => $quantity,
                    'value' => bcmul($quantity, (string) $asset->current_price, 8),
                ];
            })
            ->filter(fn (array $row) => bccomp($row['quantity'], '0', 8) > 0)
            ->values();
    }
}
