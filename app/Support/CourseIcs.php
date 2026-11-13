<?php

namespace App\Support;

use App\Models\Order;

/**
 * Kalendereintrag (.ics) für eine Kursbuchung. Ganztägig über alle Kurstage – das Ende ist nicht festgelegt,
 * der Beginn steht in der Beschreibung. Ort = Werkstatt.
 */
final class CourseIcs
{
    public static function make(Order $o): string
    {
        $c = config('ulbrich');
        $start = $o->course_start->copy();
        $end = ($o->course_end ?? $o->course_start)->copy()->addDay();   // DTEND ist bei Ganztagsterminen exklusiv
        $esc = fn (string $t) => addcslashes(str_replace(["\r\n", "\n"], '\n', $t), ',;\\');
        $place = $c['name'].', '.$c['address']['street'].', '.$c['address']['postal_code'].' '.$c['address']['locality'];
        $desc = 'Schmiedekurs bei der '.$c['name'].' · Beginn '.$c['booking']['course_start_time'].' Uhr · '.$o->participants.' '.($o->participants == 1 ? 'Person' : 'Personen')
            .' · Vorgang '.$o->number.' · Fragen: '.$c['phone']['display'];

        $lines = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Messermanufaktur Ulbrich//Kurs//DE', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:kurs-'.$o->number.'@messermanufaktur-ulbrich.de',
            'DTSTAMP:'.now('UTC')->format('Ymd\THis\Z'),
            'SEQUENCE:'.(int) $o->rebook_count,
            'DTSTART;VALUE=DATE:'.$start->format('Ymd'),
            'DTEND;VALUE=DATE:'.$end->format('Ymd'),
            'SUMMARY:'.$esc(($o->course?->name ?? 'Schmiedekurs').' – '.$c['name']),
            'LOCATION:'.$esc($place),
            'DESCRIPTION:'.$esc($desc),
            'END:VEVENT', 'END:VCALENDAR',
        ];

        return implode("\r\n", array_map(fn ($l) => self::fold($l), $lines))."\r\n";
    }

    /** Zeilen nach RFC 5545 auf 75 Oktette falten. */
    private static function fold(string $line): string
    {
        $out = '';
        while (strlen($line) > 75) {
            $cut = 75;
            while ($cut > 0 && (ord($line[$cut]) & 0xC0) === 0x80) {
                $cut--;   // kein UTF-8-Zeichen zerschneiden
            }
            $out .= substr($line, 0, $cut)."\r\n ";
            $line = substr($line, $cut);
        }

        return $out.$line;
    }
}
