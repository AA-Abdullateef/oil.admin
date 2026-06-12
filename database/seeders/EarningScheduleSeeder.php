<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\EarningSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class EarningScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $assets = Asset::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();

        if (empty($assets) || empty($users)) {
            $this->command->warn('Assets or Users not found.');
            return;
        }

        $frequencies = ['daily', 'weekly', 'monthly'];
        $statuses = ['active', 'paused'];

        for ($i = 0; $i < 10; $i++) {
            EarningSchedule::create([
                'asset_id'    => $assets[array_rand($assets)],
                'percentage'  => rand(1, 20),
                'frequency'   => $frequencies[array_rand($frequencies)],
                'start_date'  => now()->subDays(rand(1, 30)),
                'next_run_at' => now()->addDays(rand(1, 30)),
                'last_run_at' => now()->subDays(rand(1, 10)),
                'status'      => $statuses[array_rand($statuses)],
                'created_by'  => $users[array_rand($users)],
            ]);
        }
    }
}