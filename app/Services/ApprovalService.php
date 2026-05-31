<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;

class ApprovalService
{
    public function complete(Transaction $transaction, User $admin): Transaction
    {
        $transaction->update([
            'status' => Transaction::STATUS_COMPLETED,
            'updated_by' => $admin->id,
        ]);

        return $transaction->fresh(['user', 'asset', 'method', 'depositProof', 'withdrawalProof']);
    }

    public function cancel(Transaction $transaction, User $admin): Transaction
    {
        $transaction->update([
            'status' => Transaction::STATUS_CANCELLED,
            'updated_by' => $admin->id,
        ]);

        return $transaction->fresh(['user', 'asset', 'method', 'depositProof', 'withdrawalProof']);
    }
}
