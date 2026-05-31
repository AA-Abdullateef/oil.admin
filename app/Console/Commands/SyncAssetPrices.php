<?php

namespace App\Console\Commands;

use App\Services\AssetPriceService;
use Illuminate\Console\Command;

class SyncAssetPrices extends Command
{
    protected $signature = 'assets:sync-prices';

    protected $description = 'Sync current asset prices from external APIs';

    public function handle(AssetPriceService $priceService): void
    {
        $this->info('Syncing asset prices...');
        $result = $priceService->syncAll();
        $this->info("Price sync completed. Synced: {$result['synced']}; skipped: {$result['skipped']}; failed: {$result['failed']}.");
    }
}
