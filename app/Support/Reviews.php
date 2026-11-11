<?php

namespace App\Support;

use App\Models\Review;
use App\Models\Setting;

/**
 * Google-Bewertungen: Gesamtwert und wörtliche Texte, gepflegt in der Verwaltung.
 * Fallback: config('ulbrich.google_rating') / config('ulbrich.reviews').
 * Bewusst KEIN AggregateRating/Review-Schema: Google zeigt für selbst eingebundene
 * Bewertungen eines Unternehmens keine Sterne an und wertet es als irreführend.
 */
class Reviews
{
    /** @return array{value: string, count: int, as_of: string, profile_url: ?string}|null */
    public static function rating(): ?array
    {
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
        try {
            $rows = Review::query()->published()->limit($limit)->get();
        } catch (\Throwable) {
            $rows = collect();
        }
        if ($rows->isNotEmpty()) {
            return $rows->map(fn (Review $r) => [
                'text' => $r->text, 'author' => $r->author, 'date' => $r->reviewed_on->toDateString(),
                'source' => $r->source, 'rating' => $r->rating, 'topic' => $r->topic,
            ])->all();
        }

        return array_map(fn ($r) => $r + ['rating' => 5, 'topic' => null], config('ulbrich.reviews', []));
    }
}
