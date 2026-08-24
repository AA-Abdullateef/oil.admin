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
    /**
     * Write a single ledger row.
     *
     * Column responsibilities:
     *   quantity → asset units (source of truth for balance calculations)
     *   amount   → USD cost = quantity × rate  (for display and reporting only)
     *   rate     → asset price in USD at this exact moment (permanent audit record)
     *
     * Balance is always derived from SUM(quantity). amount and rate are
     * never used in balance math — only for display, reporting, and audit.
     */
    public function record(
        User    $user,
        Asset   $asset,
        string  $type,
        string  $direction,
        string  $quantity,
        ?Method $method    = null,
        ?string $rate      = null,
        ?string $reference = null,
        string  $status    = Transaction::STATUS_COMPLETED,
        ?User   $updatedBy = null,
    ): Transaction {
        // Lock in the rate at this exact moment.
        // If the caller supplies a rate (e.g. trade execution), use it.
        // Otherwise use the asset's current price.
        // Fallback to '1' for currency-type assets where price = 1 always.
        $rate ??= (string) ($asset->current_price ?: 1);

        return Transaction::create([
            'user_id'      => $user->id,
            'asset_id'     => $asset->id,
            'method_id'    => $method?->id,
            'type'         => $type,
            'direction'    => $direction,
            'quantity'     => $quantity,                    // ← asset units, stored permanently
            'amount'       => bcmul($quantity, $rate, 8),  // ← USD cost, for display only
            'rate'         => $rate,                        // ← price locked at write time
            'reference'    => $reference ?? $this->referenceFor($type, $method, $asset),
            'status'       => $status,
            'updated_by'   => $updatedBy?->id,
        ]);
    }

    /**
     * Write a pending deposit row (goes to STATUS_PENDING, awaits admin approval).
     * Uses the Deposit model which scopes to type = deposit via a global scope.
     */
    public function recordDeposit(User $user, Asset $asset, SubMethod $subMethod, string $quantity): Deposit
    {
        $rate = (string) ($asset->current_price ?: 1);

        return Deposit::create([
            'user_id'       => $user->id,
            'asset_id'      => $asset->id,
            'method_id'     => $subMethod->method_id,
            'sub_method_id' => $subMethod->id,
            'type'          => Transaction::TYPE_DEPOSIT,
            'direction'     => Transaction::DIRECTION_CREDIT,
            'quantity'      => $quantity,
            'amount'        => bcmul($quantity, $rate, 8),
            'rate'          => $rate,
            'reference'     => $this->depositReference($subMethod),
            'status'        => Transaction::STATUS_PENDING,
        ]);
    }

    /**
     * Write a pending withdrawal row (goes to STATUS_PENDING, awaits admin approval).
     * Uses the Withdrawal model which scopes to type = withdrawal via a global scope.
     */
    public function recordWithdrawal(User $user, Asset $asset, SubMethod $subMethod, string $quantity): Withdrawal
    {
        $rate = (string) ($asset->current_price ?: 1);

        return Withdrawal::create([
            'user_id'       => $user->id,
            'asset_id'      => $asset->id,
            'method_id'     => $subMethod->method_id,
            'sub_method_id' => $subMethod->id,
            'type'          => Transaction::TYPE_WITHDRAWAL,
            'direction'     => Transaction::DIRECTION_DEBIT,
            'quantity'      => $quantity,
            'amount'        => bcmul($quantity, $rate, 8),
            'rate'          => $rate,
            'reference'     => $this->withdrawalReference($subMethod),
            'status'        => Transaction::STATUS_PENDING,
        ]);
    }

    /**
     * Write two ledger rows for a buy or sell operation.
     *
     * A trade always produces exactly two rows:
     *   - One DEBIT row for the asset being given up (from_asset)
     *   - One CREDIT row for the asset being received (to_asset)
     *
     * Both rows are written atomically inside a DB transaction.
     * Both use the asset's current price as the locked-in rate for that leg.
     *
     * Example — USD → SHELL buy:
     *   debit  row: asset=USD,   direction=debit,  quantity=250,  rate=1.00,  amount=250.00
     *   credit row: asset=SHELL, direction=credit, quantity=5,    rate=50.00, amount=250.00
     */
    public function recordTrade(
        User   $user,
        Asset  $fromAsset,
        Asset  $toAsset,
        string $fromQuantity,
        string $toQuantity,
        string $type,
    ): array {
        return DB::transaction(function () use ($user, $fromAsset, $toAsset, $fromQuantity, $toQuantity, $type) {
            $reference = $this->tradeReference($type, $fromAsset, $toAsset);

            $debit = $this->record(
                user:      $user,
                asset:     $fromAsset,
                type:      $type,
                direction: Transaction::DIRECTION_DEBIT,
                quantity:  $fromQuantity,
                rate:      (string) $fromAsset->current_price,
                reference: $reference,
            );

            $credit = $this->record(
                user:      $user,
                asset:     $toAsset,
                type:      $type,
                direction: Transaction::DIRECTION_CREDIT,
                quantity:  $toQuantity,
                rate:      (string) $toAsset->current_price,
                reference: $reference,
            );

            return ['debit' => $debit, 'credit' => $credit];
        });
    }

    // ── Reference generators ──────────────────────────────────────────────────

    public function referenceFor(
        string  $type,
        ?Method $method   = null,
        ?Asset  $asset    = null,
        ?Asset  $toAsset  = null,
    ): string {
        return match ($type) {
            Transaction::TYPE_DEPOSIT    => $method
                ? $this->depositReference($method)
                : 'Deposit',
            Transaction::TYPE_WITHDRAWAL => $method
                ? $this->withdrawalReference($method)
                : 'Withdrawal',
            Transaction::TYPE_BUY,
            Transaction::TYPE_SELL       => $asset && $toAsset
                ? $this->tradeReference($type, $asset, $toAsset)
                : ucfirst($type),
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