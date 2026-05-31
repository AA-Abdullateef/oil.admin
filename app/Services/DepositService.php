<?php

namespace App\Services;

use App\Events\DepositCancelled;
use App\Events\DepositCompleted;
use App\Models\Asset;
use App\Models\Deposit;
use App\Models\DepositProof;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DepositService
{
    public function __construct(
        private readonly LedgerService $ledgerService,
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function initiate(User $user, array $data): Deposit
    {
        return DB::transaction(function () use ($user, $data) {
            $asset = Asset::findOrFail($data['asset_id']);
            $subMethod = $this->paymentMethodService->resolveSubMethod($data);

            $deposit = $this->ledgerService->recordDeposit($user, $asset, $subMethod, (string) $data['amount']);

            DepositProof::create([
                'transaction_id' => $deposit->id,
                'proof' => $data['proof'] ?? null,
            ]);

            return $deposit->load(['asset', 'method', 'subMethod.method', 'depositProof']);
        });
    }

    public function complete(Deposit $deposit, User $admin): Deposit
    {
        $deposit->update([
            'status' => Transaction::STATUS_COMPLETED,
            'updated_by' => $admin->id,
        ]);

        event(new DepositCompleted($deposit->fresh(['user', 'asset', 'method', 'subMethod.method', 'depositProof'])));

        return $deposit;
    }

    public function cancel(Deposit $deposit, User $admin, ?string $reason = null): Deposit
    {
        $deposit->update([
            'status' => Transaction::STATUS_CANCELLED,
            'updated_by' => $admin->id,
        ]);

        event(new DepositCancelled($deposit->fresh(['user', 'asset', 'method', 'subMethod.method', 'depositProof'])));

        return $deposit;
    }
}
