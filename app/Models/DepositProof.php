<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepositProof extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['transaction_id', 'proof'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
