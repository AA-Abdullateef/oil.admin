<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Deposit;
use App\Models\Method;
use App\Models\SubMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    public function record(
        User $user,
        Asset $asset,
        string $type,
        string $direction,
        string $quantity,
        ?Method $method = null,
        ?string $rate = null,
        ?string $reference = null,
        string $status = Transaction::STATUS_COMPLETED,
        ?User $updatedBy = null,
    ): Transaction {
        $rate ??= (string) ($asset->current_price ?: 1);

        return Transaction::create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'method_id' => $method?->id,
            'type' => $type,
            'direction' => $direction,
            'amount' => bcmul($quantity, $rate, 8),
            'reference' => $reference ?? $this->referenceFor($type, $method, $asset),
            'status' => $status,
            'updated_by' => $updatedBy?->id,
        ]);
    }

    public function recordDeposit(User $user, Asset $asset, SubMethod $subMethod, string $quantity): Deposit
    {
        $rate = (string) ($asset->current_price ?: 1);

        return Deposit::create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'method_id' => $subMethod->method_id,
            'sub_method_id' => $subMethod->id,
            'type' => Transaction::TYPE_DEPOSIT,
            'direction' => Transaction::DIRECTION_CREDIT,
            'amount' => bcmul($quantity, $rate, 8),
            'reference' => $this->depositReference($subMethod),
            'status' => Transaction::STATUS_PENDING,
        ]);
    }

    public function recordWithdrawal(User $user, Asset $asset, SubMethod $subMethod, string $quantity): Withdrawal
    {
        $rate = (string) ($asset->current_price ?: 1);

        return Withdrawal::create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'method_id' => $subMethod->method_id,
            'sub_method_id' => $subMethod->id,
            'type' => Transaction::TYPE_WITHDRAWAL,
            'direction' => Transaction::DIRECTION_DEBIT,
            'amount' => bcmul($quantity, $rate, 8),
            'reference' => $this->withdrawalReference($subMethod),
            'status' => Transaction::STATUS_PENDING,
        ]);
    }

    public function recordTrade(
        User $user,
        Asset $fromAsset,
        Asset $toAsset,
        string $fromQuantity,
        string $toQuantity,
        string $type,
    ): array {
        return DB::transaction(function () use ($user, $fromAsset, $toAsset, $fromQuantity, $toQuantity, $type) {
            $reference = $this->tradeReference($type, $fromAsset, $toAsset);

            $debit = $this->record(
                user: $user,
                asset: $fromAsset,
                type: $type,
                direction: Transaction::DIRECTION_DEBIT,
                quantity: $fromQuantity,
                rate: (string) $fromAsset->current_price,
                reference: $reference,
            );

            $credit = $this->record(
                user: $user,
                asset: $toAsset,
                type: $type,
                direction: Transaction::DIRECTION_CREDIT,
                quantity: $toQuantity,
                rate: (string) $toAsset->current_price,
                reference: $reference,
            );

            return ['debit' => $debit, 'credit' => $credit];
        });
    }

    public function referenceFor(string $type, ?Method $method = null, ?Asset $asset = null, ?Asset $toAsset = null): string
    {
        return match ($type) {
            Transaction::TYPE_DEPOSIT => $method ? $this->depositReference($method) : 'Deposit',
            Transaction::TYPE_WITHDRAWAL => $method ? $this->withdrawalReference($method) : 'Withdrawal',
            Transaction::TYPE_BUY,
            Transaction::TYPE_SELL => $asset && $toAsset ? $this->tradeReference($type, $asset, $toAsset) : ucfirst($type),
            default => ucfirst($type),
        };
    }

    public function depositReference(Method|SubMethod $method): string
    {
        return "Deposit via {$method->name}";
    }

    public function withdrawalReference(Method|SubMethod $method): string
    {
        return "Withdrawal to {$method->name}";
    }

    public function tradeReference(string $type, Asset $fromAsset, Asset $toAsset): string
    {
        return $type === Transaction::TYPE_BUY
            ? "Buy {$toAsset->symbol} from {$fromAsset->symbol}"
            : "Sell {$fromAsset->symbol} to {$toAsset->symbol}";
    }
}
