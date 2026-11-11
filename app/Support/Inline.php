<?php

namespace App\Support;

/**
 * Links im Fließtext der Inhaltsdateien: [Ankertext](/pfad/).
 * Nur interne Pfade aus config/pages.php werden zu Links; alles andere bleibt Text.
 * Der Text wird immer escaped (kein HTML aus den Inhaltsdateien).
 */
final class Inline
{
    private const PATTERN = '/\[([^\]]+)\]\((\/[a-z0-9\/-]*)\)/';

    public static function html(string $text): string
    {
        $pages = config('pages');
        $out = '';
        $pos = 0;
        preg_match_all(self::PATTERN, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $m) {
            $out .= e(substr($text, $pos, $m[0][1] - $pos));
            [$label, $path] = [$m[1][0], $m[2][0]];
            $out .= isset($pages[$path]) || $path === '/'
                ? '<a href="'.e($path).'">'.e($label).'</a>'
                : e($label);
            $pos = $m[0][1] + strlen($m[0][0]);
        }

        return $out.e(substr($text, $pos));
    }

    public static function plain(string $text): string
    {
        return preg_replace(self::PATTERN, '$1', $text);
    }
}
