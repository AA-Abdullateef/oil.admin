<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssetPriceService
{
    public function syncAll(): array
    {
        $result = ['synced' => 0, 'skipped' => 0, 'failed' => 0];

        Asset::where('status', Asset::STATUS_ACTIVE)
            ->whereNotNull('price_source')
            ->where('price_source', '!=', 'manual')
            ->each(function (Asset $asset) use (&$result) {
                $status = $this->syncAsset($asset);
                $result[$status]++;
            });

        return $result;
    }

    public function syncAsset(Asset $asset): string
    {
        try {
            if (! $this->hasApiCredentials($asset->price_source)) {
                Log::warning("Price sync skipped for {$asset->symbol}: missing {$asset->price_source} API key.");

                return 'skipped';
            }

            $price = match ($asset->price_source) {
                'coingecko' => $this->fetchFromCoinGecko($asset),
                'alphavantage' => $this->fetchFromAlphaVantage($asset->symbol),
                'finnhub' => $this->fetchFromFinnhub($asset->symbol),
                default => null,
            };

            if ($price === null || ! is_numeric($price) || (float) $price <= 0) {
                Log::warning("Price sync skipped for {$asset->symbol}: invalid or null price returned.");

                return 'skipped';
            }

            $price = $this->normalizePrice((string) $price);
            $asset->update(['current_price' => $price]);

            return 'synced';
        } catch (\Throwable $e) {
            Log::error("Price sync failed for {$asset->symbol}: ".$e->getMessage());

            return 'failed';
        }
    }

    public function setManually(Asset $asset, string $price): void
    {
        $price = $this->normalizePrice($price);
        $asset->update(['current_price' => $price, 'price_source' => $asset->price_source ?? 'manual']);
    }

    public function recordInitialPrice(Asset $asset): void
    {
        // Prices are stored only on assets; history is generated from the current asset state.
    }

    private function normalizePrice(string $price): string
    {
        return number_format((float) $price, 8, '.', '');
    }

    private function hasApiCredentials(?string $source): bool
    {
        return match ($source) {
            'coingecko' => filled(config('services.coingecko.key')) || config('services.coingecko.require_key') !== true,
            'alphavantage' => filled(config('services.alphavantage.key')),
            'finnhub' => filled(config('services.finnhub.key')),
            default => false,
        };
    }

    private function fetchFromCoinGecko(Asset $asset): ?string
    {
        $coinId = $asset->metadata['coingecko_id'] ?? strtolower($asset->symbol);
        $response = Http::timeout(15)->retry(2, 250)->get('https://api.coingecko.com/api/v3/simple/price', [
            'ids' => $coinId,
            'vs_currencies' => 'usd',
            'x_cg_demo_api_key' => config('services.coingecko.key'),
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json("{$coinId}.usd");
    }

    private function fetchFromAlphaVantage(string $symbol): ?string
    {
        $response = Http::timeout(15)->retry(2, 250)->get('https://www.alphavantage.co/query', [
            'function' => 'GLOBAL_QUOTE',
            'symbol' => $symbol,
            'apikey' => config('services.alphavantage.key'),
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json('Global Quote.05. price');
    }

    private function fetchFromFinnhub(string $symbol): ?string
    {
        $response = Http::timeout(15)
            ->retry(2, 250)
            ->withToken(config('services.finnhub.key'))
            ->get('https://finnhub.io/api/v1/quote', ['symbol' => $symbol]);

        if (! $response->successful()) {
            return null;
        }

        $price = $response->json('c');

        return $price > 0 ? (string) $price : null;
    }
}
