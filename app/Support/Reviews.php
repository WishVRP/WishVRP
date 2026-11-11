<?php

namespace App\Support;

use App\Models\Review;
use App\Models\Setting;

/**
 * Bewertungen für die Website.
 * Reihenfolge: 1. automatisch von Google (wenn verbunden) → 2. in der Verwaltung gepflegt → 3. config/ulbrich.php.
 * Von Hand gepflegte Bewertungen ergänzen die (max. 5) Google-Bewertungen, Dubletten (gleicher Name) entfallen.
 * Bewusst KEIN AggregateRating/Review-Schema: Google zeigt für selbst eingebundene
 * Bewertungen eines Unternehmens keine Sterne an und wertet es als irreführend.
 */
class Reviews
{
    /** @return array{value: string, count: int, as_of: string, profile_url: ?string}|null */
    public static function rating(): ?array
    {
        if ($google = GoogleReviews::get()) {
            if ($google['rating']) {
                return $google['rating'];
            }
        }
        $db = Setting::read('google_rating');

        return $db ? [
            'value' => number_format((float) $db['value'], 1, ',', ''),
            'count' => (int) $db['count'],
            'as_of' => $db['as_of'] ?? now()->format('d.m.Y'),
            'profile_url' => $db['profile_url'] ?? null,
        ] : config('ulbrich.google_rating');
    }

    /** @return array<int, array{text: string, author: string, date: string, source: string, rating: int, topic: ?string}> */
    public static function list(int $limit = 50): array
    {
        $google = GoogleReviews::get()['reviews'] ?? [];
        try {
            $manual = Review::query()->published()->limit($limit)->get()->map(fn (Review $r) => [
                'text' => $r->text, 'author' => $r->author, 'date' => $r->reviewed_on->toDateString(),
                'source' => $r->source, 'rating' => $r->rating, 'topic' => $r->topic,
            ])->all();
        } catch (\Throwable) {
            $manual = [];
        }
        if (! $google && ! $manual) {
            return array_map(fn ($r) => $r + ['rating' => 5, 'topic' => null], config('ulbrich.reviews', []));
        }
        $names = array_map(fn ($r) => mb_strtolower(trim($r['author'])), $google);
        $manual = array_filter($manual, fn ($r) => ! in_array(mb_strtolower(trim($r['author'])), $names, true));

        return array_slice([...$google, ...array_values($manual)], 0, $limit);
    }
}
