<?php

namespace Database\Seeders;

use App\Models\Method;
use Illuminate\Database\Seeder;

class MethodSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Bank Transfer', 'Cryptocurrency'] as $name) {
            Method::firstOrCreate(['name' => $name]);
        }

        $this->command->info('Payment methods seeded.');
    }
}
