<?php

namespace App\Support;

use Dompdf\Dompdf;
use Dompdf\Options;

/** PDF aus einer Blade-Vorlage (Rechnung, Gutschein, Pflegeanleitung). Keine externen Ressourcen. */
final class Pdf
{
    public static function render(string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait'): string
    {
        $options = new Options;
        $options->setIsRemoteEnabled(false);          // nichts von fremden Servern laden
        $options->setIsPhpEnabled(false);
        $options->setDefaultFont('DejaVu Sans');      // Umlaute, €
        $options->setChroot([public_path(), resource_path()]);
        $options->setTempDir(storage_path('framework/cache'));
        $options->setFontCache(storage_path('framework/cache'));

        $pdf = new Dompdf($options);
        $pdf->loadHtml(view($view, $data + ['c' => config('ulbrich'), 'logo' => self::logo()])->render(), 'UTF-8');
        $pdf->setPaper($paper, $orientation);
        $pdf->render();

        return (string) $pdf->output();
    }

    /** Monogramm als Data-URI (dompdf lädt so nichts nach). */
    public static function logo(): ?string
    {
        $file = public_path('images/icon-192.png');

        return is_file($file) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($file)) : null;
    }

    public static function eur(float|string|null $n): string
    {
        return number_format((float) $n, 2, ',', '.').' €';
    }
}
