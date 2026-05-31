<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * تشغيل جميع الـ seeders بالترتيب الصحيح
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
