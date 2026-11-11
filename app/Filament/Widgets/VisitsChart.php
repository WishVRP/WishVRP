<?php

namespace App\Filament\Widgets;

use App\Support\Stats;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class VisitsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Besucher der letzten 30 Tage';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $views = Stats::daily(30);
        $entries = Stats::daily(30, true);

        return [
            'datasets' => [
                ['label' => 'Besuche', 'data' => array_values($entries), 'borderColor' => '#2d5f93', 'backgroundColor' => 'rgba(45,95,147,0.15)', 'fill' => true, 'tension' => 0.3],
                ['label' => 'Seitenaufrufe', 'data' => array_values($views), 'borderColor' => '#c8a24a', 'backgroundColor' => 'transparent', 'tension' => 0.3],
            ],
            'labels' => array_map(fn ($d) => Carbon::parse($d)->format('d.m.'), array_keys($views)),
        ];
    }

    protected function getOptions(): array
    {
        return ['scales' => ['y' => ['beginAtZero' => true, 'suggestedMax' => 5, 'ticks' => ['precision' => 0]]]];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
