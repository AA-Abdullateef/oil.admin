<?php

namespace App\Models;

use App\Enums\EarningStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Earning extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    public const STATUS_PROCESSED = EarningStatus::Processed->value;
    public const STATUS_CANCELLED = EarningStatus::Cancelled->value;

    protected $fillable = ['user_id', 'asset_id', 'schedule_id', 'type', 'amount', 'status'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:8'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(EarningSchedule::class, 'schedule_id');
    }
}
