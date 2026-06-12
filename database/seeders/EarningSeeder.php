<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Earning;
use App\Models\EarningSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class EarningSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->toArray();
        $assets = Asset::pluck('id')->toArray();
        $schedules = EarningSchedule::pluck('id')->toArray();

        if (
            empty($users) ||
            empty($assets) ||
            empty($schedules)
        ) {
            $this->command->warn('Users, Assets or Earning Schedules not found.');
            return;
        }

        $types = [
            'daily',
            'weekly',
            'monthly',
        ];

        $statuses = [
            'pending',
            'processed',
            'cancelled',
        ];

        for ($i = 0; $i < 10; $i++) {
            Earning::create([
                'user_id'     => $users[array_rand($users)],
                'asset_id'    => $assets[array_rand($assets)],
                'schedule_id' => $schedules[array_rand($schedules)],
                'type'        => $types[array_rand($types)],
                'amount'      => rand(100, 5000),
                'status'      => $statuses[array_rand($statuses)],
                'created_at'  => now()->subDays(rand(1, 30)),
                'updated_at'  => now(),
            ]);
        }
    }
}