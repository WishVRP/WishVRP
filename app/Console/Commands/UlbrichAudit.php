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

    protected $description = 'Listet fehlende echte Unternehmensdaten ([FEHLT]) auf allen umgesetzten Seiten';

    public function handle(): int
    {
        $base = rtrim($this->option('base'), '/');
        $total = 0;

        foreach (config('pages') as $path => $page) {
            if ($page['status'] !== 'live') {
                continue;
            }

            $html = Http::get($base.$path)->body();
            preg_match_all('/\[(FEHLT|FOTO): ([^\]]+)\]/u', html_entity_decode(strip_tags($html)), $m, PREG_SET_ORDER);

            $this->line("<info>{$path}</info> – ".count($m).' Marker');
            foreach ($m as $hit) {
                $this->line("   [{$hit[1]}] ".trim(preg_replace('/\s+/', ' ', $hit[2])));
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
