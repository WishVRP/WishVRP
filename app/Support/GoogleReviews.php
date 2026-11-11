<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google-Bewertungen direkt von Google (Places API New).
 * - Abruf serverseitig, Besucher laden nichts von Google (keine Cookies, kein externes Skript, CSP bleibt dicht).
 * - Zwischengespeichert (Standard 60 Min.); danach wird im Hintergrund neu geladen, die Seite wartet nie auf Google.
 * - Google liefert Gesamtbewertung, Anzahl und höchstens 5 Bewertungen (von Google ausgewählt).
 * - Autorenfotos werden nicht geladen (Datenschutz/CSP), Name und Link zur Bewertung werden angezeigt.
 */
class GoogleReviews
{
    private const CACHE = 'google_reviews';

    private const BASE = 'https://places.googleapis.com/v1/';

    public static function enabled(): bool
    {
        return filled(config('services.google_places.key')) && filled(self::placeId());
    }

    public static function placeId(): ?string
    {
        return config('services.google_places.place_id') ?: Setting::read('google_place_id');
    }

    /** @return array{rating: ?array, reviews: array, fetched_at: string}|null */
    public static function get(): ?array
    {
        if (! self::enabled()) {
            return null;
        }
        $fresh = max(5, (int) config('services.google_places.refresh_minutes', 60)) * 60;

        // flexible: bis $fresh frisch, danach bis 24 h weiter anzeigen und im Hintergrund neu laden
        $data = Cache::flexible(self::CACHE, [$fresh, $fresh + 86400], fn () => self::fetch() ?? false);

        return $data ?: null;
    }

    public static function refresh(): ?array
    {
        Cache::forget(self::CACHE);

        return self::get();
    }

    public static function fetch(): ?array
    {
        try {
            $res = Http::timeout(6)->acceptJson()
                ->withHeaders([
                    'X-Goog-Api-Key' => config('services.google_places.key'),
                    'X-Goog-FieldMask' => 'rating,userRatingCount,googleMapsUri,reviews',
                ])
                ->get(self::BASE.'places/'.rawurlencode((string) self::placeId()), ['languageCode' => 'de']);
            if (! $res->successful()) {
                Log::warning('Google-Bewertungen: Abruf fehlgeschlagen', ['status' => $res->status(), 'error' => $res->json('error.message')]);

                return null;
            }
            $p = $res->json();
        } catch (\Throwable $e) {
            Log::warning('Google-Bewertungen: '.$e->getMessage());

            return null;
        }

        $reviews = collect($p['reviews'] ?? [])->map(function (array $r) {
            $text = $r['text']['text'] ?? $r['originalText']['text'] ?? '';
            if ($text === '') {
                return null;   // reine Sternebewertungen ohne Text nicht als Zitat zeigen
            }

            return [
                'text' => $text,
                'author' => $r['authorAttribution']['displayName'] ?? 'Google-Nutzer',
                'author_url' => self::safeUrl($r['authorAttribution']['uri'] ?? null),
                'review_url' => self::safeUrl($r['googleMapsUri'] ?? null),
                'rating' => max(1, min(5, (int) ($r['rating'] ?? 5))),
                'date' => isset($r['publishTime']) ? Carbon::parse($r['publishTime'])->toDateString() : now()->toDateString(),
                'source' => 'Google',
                'topic' => null,
            ];
        })->filter()->values()->all();

        return [
            'rating' => isset($p['rating']) ? [
                'value' => number_format((float) $p['rating'], 1, ',', ''),
                'count' => (int) ($p['userRatingCount'] ?? 0),
                'as_of' => now()->format('d.m.Y'),
                'profile_url' => self::safeUrl($p['googleMapsUri'] ?? null),
            ] : null,
            'reviews' => $reviews,
            'fetched_at' => now()->format('d.m.Y H:i'),
        ];
    }

    /** Place-ID über den Firmennamen finden (Verwaltung → „Google verbinden“). @return array<int, array{id: string, name: string, address: string}> */
    public static function search(string $query): array
    {
        $res = Http::timeout(8)->acceptJson()
            ->withHeaders([
                'X-Goog-Api-Key' => config('services.google_places.key'),
                'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress',
            ])
            ->post(self::BASE.'places:searchText', ['textQuery' => $query, 'languageCode' => 'de']);

        if (! $res->successful()) {
            throw new \RuntimeException($res->json('error.message') ?: 'Google-Suche fehlgeschlagen (Status '.$res->status().')');
        }

        return collect($res->json('places', []))->map(fn ($p) => [
            'id' => $p['id'], 'name' => $p['displayName']['text'] ?? '', 'address' => $p['formattedAddress'] ?? '',
        ])->all();
    }

    private static function safeUrl(?string $url): ?string
    {
        return $url && preg_match('#^https://([a-z0-9-]+\.)*(google\.[a-z.]+|goo\.gl)/#i', $url) ? $url : null;
    }
}
