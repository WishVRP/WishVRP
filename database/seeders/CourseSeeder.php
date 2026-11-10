<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

/**
 * Kursformate laut Live-Seite (Stand 10.11.2026, Projektskill).
 * Vor Go-live gegen /schmiedekurse/ prüfen. Pfade der Kursseiten und
 * booking_key (?kurs=) sind noch nicht bekannt → null.
 * Es werden bewusst KEINE Termine angelegt: Termine kommen aus dem
 * Buchungssystem bzw. werden in der Verwaltung gepflegt.
 */
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['name' => 'Grundlagenkurs', 'duration_label' => '1 Tag', 'blade_length_cm' => null, 'price_from' => 300],
            ['name' => 'Klingenkurs', 'duration_label' => '1 Tag', 'blade_length_cm' => null, 'price_from' => 440],
            ['name' => 'Klingenkurs „Alternativ“', 'duration_label' => null, 'blade_length_cm' => null, 'price_from' => 500],
            ['name' => 'Damast-Messerkurs', 'duration_label' => '2 Tage', 'blade_length_cm' => 32, 'price_from' => 1100],
            ['name' => 'Damast Premium-Kurs', 'duration_label' => '2–3 Tage', 'blade_length_cm' => 40, 'price_from' => 1300],
            ['name' => 'Manager-Schmiedekurs', 'duration_label' => '3 Tage', 'blade_length_cm' => null, 'price_from' => null],
        ];

        foreach ($courses as $i => $course) {
            Course::updateOrCreate(['name' => $course['name']], $course + ['sort' => ($i + 1) * 10]);
        }
    }
}
