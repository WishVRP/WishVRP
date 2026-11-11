<?php

namespace App\Support;

/** Pfade der Manufaktur-Fotos (config/fotos.php) mit sprechenden Dateinamen. */
class Foto
{
    public static function file(string $key): string
    {
        return config("fotos.{$key}.datei") ?? $key;
    }

    public static function url(string $key, int $width, string $ext = 'jpg'): string
    {
        return '/images/fotos/'.self::file($key).'-'.$width.'.'.$ext;
    }

    /** Größte JPEG-Fassung (für Schema.org, Sitemap). */
    public static function largest(string $key): string
    {
        return self::url($key, (int) max(config("fotos.{$key}.widths")));
    }
}
