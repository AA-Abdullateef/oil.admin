<?php

namespace App\Models;

use App\Enums\EarningFrequency;
use App\Enums\EarningScheduleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EarningSchedule extends Model
{
    use HasUuids, SoftDeletes;

    public const FREQUENCY_DAILY = EarningFrequency::Daily->value;
    public const FREQUENCY_WEEKLY = EarningFrequency::Weekly->value;
    public const FREQUENCY_MONTHLY = EarningFrequency::Monthly->value;

    public const STATUS_ACTIVE = EarningScheduleStatus::Active->value;
    public const STATUS_PAUSED = EarningScheduleStatus::Paused->value;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'asset_id',
        'percentage',
        'frequency',
        'start_date',
        'next_run_at',
        'last_run_at',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:4',
            'start_date' => 'datetime',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->active()->where('next_run_at', '<=', now());
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(Earning::class, 'schedule_id');
    }

    public function nextRunAfterCurrentRun(): \Illuminate\Support\Carbon
    {
        $base = $this->next_run_at ?? now();

        return match ($this->frequency) {
            self::FREQUENCY_DAILY => $base->copy()->addDay(),
            self::FREQUENCY_WEEKLY => $base->copy()->addWeek(),
            self::FREQUENCY_MONTHLY => $base->copy()->addMonthNoOverflow(),
            default => $base->copy()->addDay(),
        };
    }
}
