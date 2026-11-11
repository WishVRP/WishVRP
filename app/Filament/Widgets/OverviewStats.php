<?php

namespace App\Filament\Widgets;

use App\Models\Note;
use App\Models\Order;
use App\Support\Stats;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Auf einen Blick';

    protected ?string $description = 'Besucherzahlen ohne Cookies gezählt (eigener Server, keine IP-Adressen).';

    protected function getStats(): array
    {
        $clicks7 = Stats::clicks(7);
        $contact = ($clicks7['telefon'] ?? 0) + ($clicks7['email'] ?? 0);

        return [
            Stat::make('Besuche heute', number_format(Stats::views(1, true), 0, ',', '.'))
                ->description(number_format(Stats::views(1), 0, ',', '.').' Seitenaufrufe heute')
                ->icon(Heroicon::OutlinedUsers),
            Stat::make('Besuche 7 Tage', number_format(Stats::views(7, true), 0, ',', '.'))
                ->description(number_format(Stats::views(7), 0, ',', '.').' Seitenaufrufe')
                ->chart(array_values(Stats::daily(7, true)))->color('primary')
                ->icon(Heroicon::OutlinedChartBar),
            Stat::make('Besuche 30 Tage', number_format(Stats::views(30, true), 0, ',', '.'))
                ->description(number_format(Stats::views(30), 0, ',', '.').' Seitenaufrufe')
                ->chart(array_values(Stats::daily(30, true)))->color('primary'),
            Stat::make('Neue Vorgänge', (string) Order::query()->where('status', 'neu')->count())
                ->description(Order::query()->open()->count().' offen insgesamt')
                ->color('warning')->icon(Heroicon::OutlinedShoppingBag)
                ->url(\App\Filament\Resources\Orders\OrderResource::getUrl('index')),
            Stat::make('Bezahlt (30 Tage)', number_format(Stats::revenue(30), 2, ',', '.').' €')
                ->description(Order::query()->where('created_at', '>=', now()->subDays(30))->count().' Vorgänge in 30 Tagen')
                ->color('success')->icon(Heroicon::OutlinedBanknotes),
            Stat::make('Klicks Telefon & E-Mail (7 Tage)', (string) $contact)
                ->description(($clicks7['buchen'] ?? 0).' × „Kurs buchen“ · '.($clicks7['gutschein'] ?? 0).' × Gutschein')
                ->icon(Heroicon::OutlinedCursorArrowRays),
            Stat::make('Weihnachtsaktion', (string) \App\Models\XmasEntry::query()->count())
                ->description(\App\Models\XmasEntry::query()->whereNotNull('code')->count().' Codes · '.\App\Models\XmasEntry::query()->whereNotNull('redeemed_at')->count().' eingelöst')
                ->icon(Heroicon::OutlinedGift)
                ->url(\App\Filament\Resources\XmasEntries\XmasEntryResource::getUrl('index')),
            Stat::make('Offene Notizen', (string) Note::query()->open()->count())
                ->description('für alle sichtbar')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->url(\App\Filament\Resources\Notes\NoteResource::getUrl('index')),
        ];
    }
}
