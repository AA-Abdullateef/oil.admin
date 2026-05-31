<?php

namespace App\Services;

use App\Models\Earning;
use App\Models\EarningSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EarningService
{
    public function __construct(private readonly BalanceService $balanceService) {}

    public function processDueSchedules(): int
    {
        $processed = 0;

        EarningSchedule::due()
            ->orderBy('next_run_at')
            ->each(function (EarningSchedule $schedule) use (&$processed) {
                $this->processSchedule($schedule);
                $processed++;
            });

        return $processed;
    }

    public function processSchedule(EarningSchedule $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            $lockedSchedule = EarningSchedule::whereKey($schedule->id)->lockForUpdate()->firstOrFail();

            if ($lockedSchedule->status !== EarningSchedule::STATUS_ACTIVE || $lockedSchedule->next_run_at->isFuture()) {
                return;
            }

            User::query()
                ->orderBy('id')
                ->chunkById(200, function ($users) use ($lockedSchedule) {
                    foreach ($users as $user) {
                        $balance = $this->balanceService->getBalance($user, $lockedSchedule->asset_id);

                        if (bccomp($balance, '0', 8) <= 0) {
                            continue;
                        }

                        $amount = bcdiv(bcmul($balance, (string) $lockedSchedule->percentage, 8), '100', 8);

                        if (bccomp($amount, '0', 8) <= 0) {
                            continue;
                        }

                        Earning::create([
                            'user_id' => $user->id,
                            'asset_id' => $lockedSchedule->asset_id,
                            'schedule_id' => $lockedSchedule->id,
                            'type' => $lockedSchedule->frequency,
                            'amount' => $amount,
                            'status' => Earning::STATUS_PROCESSED,
                        ]);
                    }
                });

            $lockedSchedule->update([
                'last_run_at' => now(),
                'next_run_at' => $lockedSchedule->nextRunAfterCurrentRun(),
            ]);
        });
    }
}
