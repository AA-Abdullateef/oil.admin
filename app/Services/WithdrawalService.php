<?php

namespace App\Services;

use App\Events\WithdrawalCancelled;
use App\Events\WithdrawalProcessing;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Asset;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\WithdrawalProof;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function __construct(
        private readonly BalanceService $balanceService,
        private readonly LedgerService $ledgerService,
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function request(User $user, array $data): Withdrawal
    {
        return DB::transaction(function () use ($user, $data) {
            $asset = Asset::findOrFail($data['asset_id']);
            $subMethod = $this->paymentMethodService->resolveSubMethod($data);

            $hasPendingWithdrawal = Transaction::query()
                ->where('user_id', $user->id)
                ->where('type', Transaction::TYPE_WITHDRAWAL)
                ->where('asset_id', $asset->id)
                ->where('status', Transaction::STATUS_PENDING)
                ->exists();

            if ($hasPendingWithdrawal) {
                throw ValidationException::withMessages([
                    'asset_id' => [
                        "You already have a pending {$asset->symbol} withdrawal request.",
                    ],
                ]);
            }

            $available = $this->balanceService->for($user, $asset);

            if (bccomp($available, (string) $data['amount'], 8) < 0) {
                throw new InsufficientBalanceException("Insufficient {$asset->symbol} balance for this withdrawal.");
            }

            $withdrawal = $this->ledgerService->recordWithdrawal($user, $asset, $subMethod, (string) $data['amount']);

            WithdrawalProof::create([
                'transaction_id' => $withdrawal->id,
                'destination_type' => $data['destination_type'],
                'account_name' => $data['account_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'wallet_address' => $data['wallet_address'] ?? null,
                'network' => $data['network'] ?? null,
            ]);

            return $withdrawal->load(['asset', 'method', 'subMethod.method', 'withdrawalProof']);
        });
    }

    public function cancel(Withdrawal $withdrawal, User $admin, ?string $reason = null): Withdrawal
    {
        $withdrawal->update([
            'status' => Transaction::STATUS_CANCELLED,
            'updated_by' => $admin->id,
        ]);

        event(new WithdrawalCancelled($withdrawal->fresh(['user', 'asset', 'method', 'subMethod.method', 'withdrawalProof'])));

        return $withdrawal;
    }

    public function process(Withdrawal $withdrawal, User $admin, array $evidence = []): Withdrawal
    {
        $withdrawal->update([
            'status' => Transaction::STATUS_PROCESSING,
            'updated_by' => $admin->id,
        ]);

        if (isset($evidence['payment_evidence'])) {
            $withdrawal->withdrawalProof()->update(['proof' => $evidence['payment_evidence']]);
        }

        event(new WithdrawalProcessing($withdrawal->fresh(['user', 'asset', 'method', 'subMethod.method', 'withdrawalProof'])));

        return $withdrawal;
    }
}
