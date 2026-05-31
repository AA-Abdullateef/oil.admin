<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Asset;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TradeService
{
    public function __construct(
        private readonly BalanceService $balanceService,
        private readonly LedgerService $ledgerService,
    ) {}

    public function buy(User $user, array $data): array
    {
        return $this->convert($user, $data['from_asset_id'], $data['to_asset_id'], (string) $data['amount'], Transaction::TYPE_BUY);
    }

    public function sell(User $user, array $data): array
    {
        return $this->convert($user, $data['from_asset_id'], $data['to_asset_id'], (string) $data['amount'], Transaction::TYPE_SELL);
    }

    private function convert(User $user, string $fromAssetId, string $toAssetId, string $quantity, string $type): array
    {
        return DB::transaction(function () use ($user, $fromAssetId, $toAssetId, $quantity, $type) {
            $fromAsset = Asset::findOrFail($fromAssetId);
            $toAsset = Asset::findOrFail($toAssetId);

            $this->assertTradable($fromAsset, 'from_asset_id');
            $this->assertTradable($toAsset, 'to_asset_id');

            $available = $this->balanceService->getBalance($user, $fromAsset);
            if (bccomp($available, $quantity, 8) < 0) {
                throw new InsufficientBalanceException("Insufficient {$fromAsset->symbol} balance.");
            }

            $fromValue = bcmul($quantity, (string) $fromAsset->current_price, 8);
            $receivedQuantity = bcdiv($fromValue, (string) $toAsset->current_price, 8);
            $transactions = $this->ledgerService->recordTrade(
                user: $user,
                fromAsset: $fromAsset,
                toAsset: $toAsset,
                fromQuantity: $quantity,
                toQuantity: $receivedQuantity,
                type: $type,
            );

            return [
                'debit' => $transactions['debit']->load('asset'),
                'credit' => $transactions['credit']->load('asset'),
            ];
        });
    }

    private function assertTradable(Asset $asset, string $field): void
    {
        if (! $asset->isTradable()) {
            throw ValidationException::withMessages([$field => "{$asset->symbol} is not tradable."]);
        }
    }
}
