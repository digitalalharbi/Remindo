<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            CategorySeeder::class,
            AdminSeeder::class,
        ]);

        // Demo data only outside production.
        if (! app()->environment('production')) {
            $this->call(DemoSeeder::class);
        }
    }
}
