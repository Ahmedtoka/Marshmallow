<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Base content for a fresh install. Demo leads and traffic are separate:
     * php artisan db:seed --class=DemoDataSeeder (local only).
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            BranchSeeder::class,
            UserSeeder::class,
            ClassroomSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
