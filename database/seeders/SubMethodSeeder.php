<?php

namespace Database\Seeders;

use App\Models\Method;
use App\Models\SubMethod;
use Illuminate\Database\Seeder;

class SubMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = Method::all();

        foreach ($methods as $method) {
            for ($i = 0; $i < 3; $i++) {
                SubMethod::factory()->create(['method_id' => $method->id]);
            }
        }

        $this->command->info('Sub-methods seeded.');
    }
}