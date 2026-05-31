<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Method;
use App\Models\Transaction;
use App\Models\User;

class TransactionService
{
    public function __construct(private readonly LedgerService $ledgerService) {}

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
        return $this->ledgerService->record(
            user: $user,
            asset: $asset,
            type: $type,
            direction: $direction,
            quantity: $quantity,
            method: $method,
            rate: $rate,
            reference: $reference,
            status: $status,
            updatedBy: $updatedBy,
        );
    }
}
