<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Go-live-Prüfung: rendert alle 'live'-Seiten und listet sichtbare
 * [FEHLT]-Marker sowie Foto-Platzhalter. Exit-Code 1, solange etwas fehlt.
 *   php artisan ulbrich:audit --base=http://127.0.0.1:8000
 */
class UlbrichAudit extends Command
{
    protected $signature = 'ulbrich:audit {--base=http://127.0.0.1:8000}';

    protected $description = 'Listet fehlende echte Unternehmensdaten und Bildplätze auf allen umgesetzten Seiten';

    public function handle(): int
    {
        $base = rtrim($this->option('base'), '/');
        $total = 0;

        foreach (config('pages') as $path => $page) {
            if ($page['status'] !== 'live') {
                continue;
            }

            $html = Http::get($base.$path)->body();
            // data-missing = fehlende Angabe, data-placeholder = Bildplatz/Platzhalter (öffentlich neutral gestaltet)
            preg_match_all('/data-(missing|placeholder)="([^"]*)"/u', $html, $m, PREG_SET_ORDER);

            $this->line("<info>{$path}</info> – ".count($m).' offene Stellen');
            foreach ($m as $hit) {
                $kind = $hit[1] === 'missing' ? 'FEHLT' : 'BILD/PLATZHALTER';
                $this->line("   [{$kind}] ".html_entity_decode(trim($hit[2])));
            }
            $total += count($m);
        }

        $planned = collect(config('pages'))->where('status', 'planned')->count();
        $this->newLine();
        $this->line("Marker gesamt: {$total} · noch nicht umgesetzte Bestandsseiten: {$planned}");
        $this->line('Steuermodus: '.config('ulbrich.tax_mode').(config('ulbrich.tax_mode') === 'pending' ? ' (offen)' : ''));

        return ($total === 0 && $planned === 0) ? self::SUCCESS : self::FAILURE;
    }
}
