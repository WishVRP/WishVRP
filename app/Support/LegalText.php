<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Rechtstexte aus resources/legal/{slug}.md (Arbeitsfassung des Betreibers, Stand 12.11.2026).
 * Platzhalter in eckigen Großbuchstaben, z. B. [LOG-SPEICHERFRIST ERGÄNZEN], werden NIE angezeigt:
 * Sie werden zu einem neutralen „Angabe folgt“ mit data-missing (php artisan ulbrich:audit listet sie).
 */
final class LegalText
{
    public static function html(string $slug): string
    {
        $md = (string) @file_get_contents(resource_path('legal/'.$slug.'.md'));
        $md = preg_replace('/^# .+\R+/u', '', $md, 1);                       // H1 kommt aus dem Seitenlayout
        $missing = [];
        $md = preg_replace_callback('/\*{0,2}\[((?:VOR |[A-ZÄÖÜ]{2,})[^\]]{6,})\](?!\()\*{0,2}/u', function ($m) use (&$missing) {
            $missing[] = trim($m[1]);

            return '%%MISSING'.(count($missing) - 1).'%%';
        }, $md);
        $md = preg_replace('/\s*\{#([a-z0-9-]+)\}\s*$/m', ' %%ID:$1%%', $md);    // Überschriften-Anker {#id}
        $html = Str::markdown($md, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $html = preg_replace('/<h([23])>(.*?) %%ID:([a-z0-9-]+)%%<\/h\1>/u', '<h$1 id="$3">$2</h$1>', $html);
        foreach ($missing as $i => $text) {
            $html = str_replace('%%MISSING'.$i.'%%', '<span class="pending" data-missing="'.e($text).'">Angabe folgt</span>', $html);
        }

        return $html;
    }
}
