<?php

namespace App\Models;

use App\Enums\TransactionDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasUuids, SoftDeletes;

    public const TYPE_DEPOSIT = TransactionType::Deposit->value;
    public const TYPE_WITHDRAWAL = TransactionType::Withdrawal->value;
    public const TYPE_BUY = TransactionType::Buy->value;
    public const TYPE_SELL = TransactionType::Sell->value;

    public const DIRECTION_CREDIT = TransactionDirection::Credit->value;
    public const DIRECTION_DEBIT = TransactionDirection::Debit->value;

    public const STATUS_PENDING = TransactionStatus::Pending->value;
    public const STATUS_PROCESSING = TransactionStatus::Processing->value;
    public const STATUS_COMPLETED = TransactionStatus::Completed->value;
    public const STATUS_CANCELLED = TransactionStatus::Cancelled->value;

    public const BALANCE_STATUSES = [
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'asset_id',
        'method_id',
        'sub_method_id',
        'type',
        'direction',
        'amount',
        'reference',
        'status',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
        ];
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::BALANCE_STATUSES);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(Method::class);
    }

    public function subMethod(): BelongsTo
    {
        return $this->belongsTo(SubMethod::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function depositProof(): HasOne
    {
        return $this->hasOne(DepositProof::class, 'transaction_id', 'id');
    }

    public function withdrawalProof(): HasOne
    {
        return $this->hasOne(WithdrawalProof::class, 'transaction_id', 'id');
    }

    public function getUuidReferenceAttribute(): ?string
    {
        return $this->reference;
    }

    public function getCategoryAttribute(): string
    {
        return $this->type;
    }

    public function getCurrencyAttribute(): string
    {
        return $this->asset?->symbol ?? '';
    }

    public function getRateAttribute(): string
    {
        return number_format((float) ($this->asset?->current_price ?: 0), 8, '.', '');
    }

    public function getQuantityAttribute(): string
    {
        $rate = (string) ($this->asset?->current_price ?: 0);

        if (bccomp($rate, '0', 8) <= 0) {
            return '0.00000000';
        }

        return bcdiv((string) $this->amount, $rate, 8);
    }
}
