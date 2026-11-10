<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Nur echte Stammdaten. Kein Admin-Konto mit Standardpasswort:
     * Verwaltungszugang wird per `php artisan make:filament-user` angelegt.
     */
    public function run(): void
    {
        $this->call(CourseSeeder::class);
    }
}
