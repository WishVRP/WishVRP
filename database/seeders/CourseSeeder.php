<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

/**
 * Kursformate laut Live-Seite messermanufaktur-ulbrich.de (abgerufen 11.11.2026, Kursseiten unter /schmiedekurse/…/).
 * total_length_cm = Gesamtlänge inkl. Griff (so auf der Live-Seite angegeben).
 * booking_key = Kursname, weil die Live-Buchung ?kurs=<Name> verwendet.
 * Es werden bewusst KEINE Termine angelegt: Termine kommen aus dem Buchungssystem bzw. der Verwaltung.
 */
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['name' => 'Grundlagenkurs', 'slug' => 'grundlagenkurs', 'category' => 'einstieg', 'duration_label' => '1 Tag', 'hours_label' => 'etwa 8 Stunden', 'total_length_cm' => null, 'price_from' => 300,
                'project' => 'Selbstgeschmiedetes Werkstück',
                'inclusions' => ['Einführung in den Umgang mit Werkstatt und Werkzeug', 'Arbeit am eigenen Werkstück unter Anleitung', 'Getränke vor Ort']],
            ['name' => 'Klingenkurs', 'slug' => 'klingenkurs', 'category' => 'klinge', 'duration_label' => '1 Tag', 'hours_label' => 'etwa 8 Stunden', 'total_length_cm' => null, 'price_from' => 440,
                'project' => 'Zwei selbstgeschmiedete Klingen',
                'inclusions' => ['Arbeit an zwei Klingen', 'Aufbau auf den Grundlagen des Schmiedens', 'Getränke vor Ort; Griff-Fertigung gegen Aufpreis']],
            ['name' => 'Klingenkurs „Alternativ“', 'slug' => 'klingenkurs-alternativ', 'category' => 'klinge', 'duration_label' => '1 Tag', 'hours_label' => 'etwa 8 Stunden', 'total_length_cm' => null, 'price_from' => 500,
                'project' => 'Ergebnis gemäß veröffentlichtem Termin',
                'inclusions' => ['Eigenständiges Kursformat', 'Getränke vor Ort', 'Werkstück, Material und Griffumfang laut konkretem Termin']],
            ['name' => 'Damast-Messerkurs', 'slug' => 'damast-messerkurs', 'category' => 'damast', 'duration_label' => '2 Tage', 'hours_label' => 'jeweils etwa 8 Stunden', 'total_length_cm' => 32, 'price_from' => 1100,
                'project' => 'Damastmesser bis 32 cm Gesamtlänge inklusive Griff',
                'inclusions' => ['Damastmesser bis 32 cm Gesamtlänge inklusive Griff', 'Zwei Kurstage mit jeweils etwa acht Stunden', 'Eine Übernachtung und Getränke vor Ort']],
            ['name' => 'Damast Premium-Kurs', 'slug' => 'damast-premium-kurs', 'category' => 'damast', 'duration_label' => '2–3 Tage', 'hours_label' => null, 'total_length_cm' => 40, 'price_from' => 1300,
                'project' => 'Damastmesser bis 40 cm Gesamtlänge',
                'inclusions' => ['Damastprojekt bis 40 cm Gesamtlänge', 'Zwei bis drei Kurstage nach Termin', 'Ein bis zwei Übernachtungen und Getränke vor Ort']],
            ['name' => 'Manager-Schmiedekurs', 'slug' => 'manager-schmiedekurs', 'category' => 'gruppe', 'duration_label' => '3 Tage', 'hours_label' => 'jeweils etwa 8 Stunden', 'total_length_cm' => 40, 'price_from' => null,
                'project' => 'Damastmesser bis 40 cm Gesamtlänge',
                'inclusions' => ['Drei Kurstage mit jeweils etwa acht Stunden', 'Damastprojekt bis 40 cm Gesamtlänge', 'Übernachtungen nach individuellem Angebot']],
        ];

        foreach ($courses as $i => $course) {
            Course::updateOrCreate(['name' => $course['name']], $course + [
                'sort' => ($i + 1) * 10,
                'path' => '/schmiedekurse/'.$course['slug'].'/',
                'booking_key' => $course['name'],
            ]);
        }
    }
}
