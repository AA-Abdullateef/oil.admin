<?php

namespace App\Console\Commands;

use App\Services\EarningService;
use Illuminate\Console\Command;

class ProcessEarningSchedules extends Command
{
    protected $signature = 'earnings:process-schedules';

    protected $description = 'Process due earning schedules.';

    public function handle(EarningService $earningService): int
    {
        $processed = $earningService->processDueSchedules();
        $this->info("Processed {$processed} earning schedule(s).");

        return self::SUCCESS;
    }
}
