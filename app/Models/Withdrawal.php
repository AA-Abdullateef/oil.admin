<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Withdrawal extends Transaction
{
    protected $table = 'transactions';

    protected static function booted(): void
    {
        static::addGlobalScope('withdrawal', fn (Builder $builder) => $builder->where('type', self::TYPE_WITHDRAWAL));
    }

    public function getWithdrawalMethodAttribute(): ?string
    {
        return $this->subMethod?->name ?? $this->method?->name;
    }

    public function getWalletAddressOrBankAttribute(): string
    {
        $proof = $this->withdrawalProof;

        if (! $proof) {
            return '';
        }

        return $proof->destination_type === 'bank'
            ? trim("{$proof->bank_name} {$proof->account_number}")
            : trim("{$proof->network} {$proof->wallet_address}");
    }

    public function getPaymentEvidenceAttribute(): ?string
    {
        return $this->withdrawalProof?->proof;
    }

    public function getAdminNotesAttribute(): ?string
    {
        return null;
    }
}
