<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Deposit extends Transaction
{
    protected $table = 'transactions';

    protected static function booted(): void
    {
        static::addGlobalScope('deposit', fn (Builder $builder) => $builder->where('type', self::TYPE_DEPOSIT));
    }

    public function getPaymentMethodAttribute(): ?string
    {
        return $this->subMethod?->name ?? $this->method?->name;
    }

    public function getProofAttribute(): ?string
    {
        return $this->depositProof?->proof;
    }

    public function getUpdatedByIdAttribute(): ?string
    {
        return $this->updated_by;
    }
}
