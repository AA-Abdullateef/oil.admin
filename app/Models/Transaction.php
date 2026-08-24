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

    public const TYPE_DEPOSIT    = TransactionType::Deposit->value;
    public const TYPE_WITHDRAWAL = TransactionType::Withdrawal->value;
    public const TYPE_BUY        = TransactionType::Buy->value;
    public const TYPE_SELL       = TransactionType::Sell->value;

    public const DIRECTION_CREDIT = TransactionDirection::Credit->value;
    public const DIRECTION_DEBIT  = TransactionDirection::Debit->value;

    public const STATUS_PENDING    = TransactionStatus::Pending->value;
    public const STATUS_PROCESSING = TransactionStatus::Processing->value;
    public const STATUS_COMPLETED  = TransactionStatus::Completed->value;
    public const STATUS_CANCELLED  = TransactionStatus::Cancelled->value;

    /**
     * Statuses that count toward a user's spendable balance.
     * Pending is excluded — only confirmed activity moves the balance.
     * Exception: pending withdrawals are deducted in BalanceService separately
     * to lock funds immediately on request.
     */
    public const BALANCE_STATUSES = [
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
    ];

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'user_id',
        'asset_id',
        'method_id',
        'sub_method_id',
        'type',
        'direction',
        'quantity',   // ← asset units (the source of truth for balance)
        'amount',     // ← USD cost value (quantity × rate, for display/reporting)
        'rate',       // ← asset price in USD at transaction time (for audit/display)
        'reference',
        'status',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
            'amount'   => 'decimal:8',
            'rate'     => 'decimal:8',
        ];
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::BALANCE_STATUSES);
    }

    // ── Relationships ────────────────────────────────────────────────────────

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

    // ── Accessors ────────────────────────────────────────────────────────────

    /**
     * Alias kept for backward compatibility with resources and views
     * that reference $transaction->uuid_reference.
     */
    public function getUuidReferenceAttribute(): ?string
    {
        return $this->reference;
    }

    /**
     * Alias used by some resources for the transaction type field.
     */
    public function getCategoryAttribute(): string
    {
        return $this->type;
    }

    /**
     * The asset symbol — used in legacy resource fields.
     */
    public function getCurrencyAttribute(): string
    {
        return $this->asset?->symbol ?? '';
    }

    // ── REMOVED: getQuantityAttribute() ──────────────────────────────────────
    // The old computed accessor derived quantity from amount ÷ current_price.
    // This was broken: the result changed every time the asset price changed,
    // making a user's share count fluctuate without any trade occurring.
    //
    // quantity is now a real stored column written at insert time.
    // It represents the immutable historical fact of how many asset units
    // this transaction moved.  It never changes after creation.
    //
    // ── REMOVED: getRateAttribute() ──────────────────────────────────────────
    // The old accessor returned the CURRENT asset price, not the price at the
    // time of the transaction. This was misleading — a trade shown in history
    // appeared to have executed at today's price, not the actual trade price.
    //
    // rate is now a real stored column written at insert time.
    // It represents the asset price in USD at the exact moment the transaction
    // was created — a permanent audit record.
}