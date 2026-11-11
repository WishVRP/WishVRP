<?php

namespace App\Filament\Widgets;

use App\Models\SiteEvent;
use App\Support\Stats;
use Filament\Widgets\Widget;

class TrafficDetails extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.traffic-details';

    protected function getViewData(): array
    {
        $label = fn (array $rows, array $names) => collect($rows)->mapWithKeys(fn ($n, $k) => [$names[$k] ?? $k => $n])->all();

        return [
            'pages' => Stats::topPages(30),
            'sources' => $label(Stats::by('source', 30), Stats::SOURCES),
            'devices' => $label(Stats::by('device', 30), Stats::DEVICES),
            'clicks' => $label(Stats::clicks(30), SiteEvent::NAMES),
            'referrers' => Stats::by('referrer_host', 30),
        ];
    }
}
