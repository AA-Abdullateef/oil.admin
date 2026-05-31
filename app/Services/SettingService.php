<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const CACHE_KEY = 'platform_settings';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a single setting value by key.
     * Returns $default if the key does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        return $settings[$key] ?? $default;
    }

    /**
     * Get all settings as key => typed_value map.
     * Cached to avoid repeated DB hits on every request.
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::all()
                ->mapWithKeys(fn (Setting $s) => [$s->key => $s->typedValue()])
                ->toArray();
        });
    }

    /**
     * Get all settings in a given group, keyed by key.
     */
    public function group(string $group): array
    {
        return Setting::where('group', $group)
            ->get()
            ->mapWithKeys(fn (Setting $s) => [$s->key => $s->typedValue()])
            ->toArray();
    }

    /**
     * Get all public settings (safe to expose to unauthenticated API consumers).
     */
    public function public(): array
    {
        return Setting::where('is_public', true)
            ->where('group', '!=', 'payment')
            ->get()
            ->groupBy('group')
            ->map(fn ($group) =>
                $group->mapWithKeys(fn (Setting $s) => [$s->key => $s->typedValue()])
            )
            ->toArray();
    }

    /**
     * Set a single value and bust the cache.
     */
    public function set(string $key, mixed $value): void
    {
        Setting::where('key', $key)->update(['value' => $value]);
        $this->bust();
    }

    /**
     * Bulk update from an array of key => value pairs.
     */
    public function bulkSet(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }
        $this->bust();
    }

    public function bust(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
