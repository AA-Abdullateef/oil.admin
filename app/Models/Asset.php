<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Asset extends Model
{
    use HasUuids, SoftDeletes;

    public const TYPE_CURRENCY = AssetType::Currency->value;
    public const TYPE_CRYPTO = AssetType::Crypto->value;
    public const TYPE_SHARE = AssetType::Share->value;
    public const TYPE_COMMODITY = AssetType::Commodity->value;

    public const STATUS_ACTIVE = AssetStatus::Active->value;
    public const STATUS_INACTIVE = AssetStatus::Inactive->value;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'symbol',
        'name',
        'type',
        'current_price',
        'price_source',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'current_price' => 'decimal:8',
            'metadata' => 'array',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function earningSchedules(): HasMany
    {
        return $this->hasMany(EarningSchedule::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(Earning::class);
    }

    public function isTradable(): bool
    {
        return $this->status === self::STATUS_ACTIVE && bccomp((string) $this->current_price, '0', 8) > 0;
    }

    public function dynamicPriceHistory(int $days = 30): Collection
    {
        $recordedAt = $this->updated_at ?? now();

        return collect(range($days - 1, 0))
            ->map(fn (int $daysAgo) => (object) [
                'price' => $this->current_price,
                'source' => $this->price_source,
                'recorded_at' => $recordedAt->copy()->subDays($daysAgo),
            ]);
    }
}
