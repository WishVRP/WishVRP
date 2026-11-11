<?php

namespace App\Support;

use App\Models\CourseDate;
use App\Models\EditionPiece;

/** Inhalte der automatischen Wochen-Ausgabe – ausschließlich aus echten Daten der Website. */
final class NewsletterContent
{
    /** @return array<string, mixed> */
    public static function weekly(?int $discount = null): array
    {
        $dates = CourseDate::query()->with('course')->upcoming()->limit(6)->get()->map(fn ($d) => [
            'course' => $d->course?->name,
            'date' => $d->starts_on->locale('de')->isoFormat('dd, D. MMMM YYYY'),
            'seats' => $d->seatsLeftLabel(),
            'url' => PageMeta::absolute($d->course?->bookingUrl() ?? '/schmiedekurse/buchen/'),
        ])->all();

        $free = EditionPiece::grid()->filter->isSelectable()->count();

        $guides = collect(config('pages'))
            ->filter(fn ($p, $path) => str_starts_with($path, '/ratgeber/') && $path !== '/ratgeber/' && $p['status'] === 'live')
            ->map(fn ($p, $path) => ['title' => $p['title'], 'text' => $p['description'], 'url' => PageMeta::absolute($path)])
            ->values();
        $tip = $guides->isNotEmpty() ? $guides[(int) now()->isoWeek() % $guides->count()] : null;

        return [
            'dates' => $dates,
            'edition' => ['free' => $free, 'regular' => (int) config('ulbrich.edition.regular'), 'price' => (int) config('ulbrich.edition.price'), 'url' => PageMeta::absolute('/klappmesser/')],
            'tip' => $tip,
            'discount' => $discount,
        ];
    }

    public static function subject(?int $discount): string
    {
        $week = now()->isoWeek();

        return $discount
            ? "{$discount} % Rabatt für dich – Neues aus der Messermanufaktur (KW {$week})"
            : "Neues aus der Messermanufaktur Ulbrich (KW {$week})";
    }

    /** @param array<string, mixed> $b */
    public static function preheader(array $b): string
    {
        $parts = [];
        if (! empty($b['discount'])) {
            $parts[] = "Dein persönlicher {$b['discount']}-%-Code";
        }
        $parts[] = $b['dates'] ? count($b['dates']).' Kurstermine mit freien Plätzen' : 'Kurstermine auf Anfrage';
        $parts[] = "Edition 100: {$b['edition']['free']} Nummern frei";

        return implode(' · ', $parts);
    }
}
