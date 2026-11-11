<?php

namespace App\Support;

/**
 * JSON-LD-Bausteine. Regeln:
 *  - nur Angaben, die auch sichtbar auf der Seite stehen
 *  - fehlende Werte (null) werden weggelassen, nie als Platzhalter ausgegeben
 *  - kein AggregateRating/Review (keine eigenen Bewertungen auf der Seite)
 */
final class Schema
{
    public static function businessId(): string
    {
        return PageMeta::absolute('/').'#organization';
    }

    public static function personId(): string
    {
        return PageMeta::absolute('/').'#andreas-ulbrich';
    }

    /** @return array<string, mixed> */
    public static function localBusiness(): array
    {
        $c = config('ulbrich');

        $hours = array_map(fn (array $h) => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $h['days'],
            'opens' => $h['opens'],
            'closes' => $h['closes'],
        ], $c['opening_hours']);

        return self::clean([
            '@type' => 'LocalBusiness',
            '@id' => self::businessId(),
            'name' => $c['name'],
            'url' => PageMeta::absolute('/'),
            'logo' => PageMeta::absolute('/images/icon-512.png'),
            'image' => PageMeta::absolute(config('seo.default_og_image')),
            'telephone' => $c['phone']['e164'],
            'email' => $c['email'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $c['address']['street'],
                'postalCode' => $c['address']['postal_code'],
                'addressLocality' => $c['address']['locality'],
                'addressRegion' => $c['address']['region'],
                'addressCountry' => $c['address']['country'],
            ],
            'openingHoursSpecification' => $hours,
            'founder' => ['@id' => self::personId()],
            'description' => "Schmiedekurse und handgefertigte Messer von {$c['owner_role']} {$c['owner']} – Schmiedehandwerk seit {$c['heritage']['since']} in der Familie, heute in {$c['heritage']['generation']}. Generation.",
            'knowsAbout' => ['Messerschmieden', 'Damaststahl', 'Klappmesser', 'Schmiedekurse'],
            'sameAs' => $c['same_as'] ?: null,
        ]);
    }

    /** @return array<string, mixed> */
    public static function person(): array
    {
        $c = config('ulbrich');

        return [
            '@type' => 'Person',
            '@id' => self::personId(),
            'name' => $c['owner'],
            'jobTitle' => $c['owner_role'],
            'worksFor' => ['@id' => self::businessId()],
            'memberOf' => ['@type' => 'Organization', 'name' => $c['chamber']],
            'description' => "{$c['owner_role']} in {$c['heritage']['generation']}. Generation – Schmiedehandwerk seit {$c['heritage']['since']} in der Familie.",
            'knowsAbout' => ['Messerschmieden', 'Damaststahl', 'Klappmesser', 'Schmiedekurse'],
        ];
    }

    /** @return array<string, mixed> */
    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => PageMeta::absolute('/').'#website',
            'url' => PageMeta::absolute('/'),
            'name' => config('ulbrich.name'),
            'inLanguage' => 'de-DE',
            'publisher' => ['@id' => self::businessId()],
        ];
    }

    /** @return array<string, mixed> */
    public static function webPage(PageMeta $meta): array
    {
        return [
            '@type' => 'WebPage',
            '@id' => $meta->canonical().'#webpage',
            'url' => $meta->canonical(),
            'name' => $meta->fullTitle(),
            'description' => $meta->description,
            'inLanguage' => 'de-DE',
            'isPartOf' => ['@id' => PageMeta::absolute('/').'#website'],
            'about' => ['@id' => self::businessId()],
        ];
    }

    /** @return array<string, mixed>|null */
    public static function breadcrumb(PageMeta $meta): ?array
    {
        if ($meta->breadcrumb === []) {
            return null;
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $item, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => PageMeta::absolute($item['path']),
            ], $meta->breadcrumb, array_keys($meta->breadcrumb)),
        ];
    }

    /**
     * @param  array<int, array{q: string, a: string}>  $faqs  exakt die sichtbaren Fragen/Antworten
     * @return array<string, mixed>
     */
    public static function faq(array $faqs): array
    {
        return [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f['a'])],
            ], $faqs),
        ];
    }

    /**
     * Kursangebote als ItemList von Course (nur Name, Kategorie, Dauer, Anbieter – sichtbare Angaben).
     *
     * @return array<string, mixed>
     */
    public static function courseList(\Illuminate\Support\Collection $courses): array
    {
        return [
            '@type' => 'ItemList',
            'name' => 'Schmiedekurse der '.config('ulbrich.name'),
            'itemListElement' => $courses->values()->map(fn ($course, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'item' => self::clean([
                    '@type' => 'Course',
                    'name' => $course->name,
                    'description' => trim($course->name.' – '.collect([$course->categoryLabel(), $course->duration_label])->filter()->implode(', ')),
                    'url' => PageMeta::absolute('/schmiedekurse/').'#'.\Illuminate\Support\Str::slug($course->name),
                    'provider' => ['@id' => self::businessId()],
                ]),
            ])->all(),
        ];
    }

    /**
     * Edition 100 als Product. Verfügbarkeit ist nicht bekannt → wird weggelassen.
     *
     * @param  array<string, mixed>  $e
     * @return array<string, mixed>
     */
    public static function product(array $e): array
    {
        return self::clean([
            '@type' => 'Product',
            'name' => $e['name'],
            'description' => "Damast-Klappmesser, limitiert auf {$e['total']} nummerierte Stücke ({$e['regular']} reguläre Stücke, {$e['special']} Sonderstücke).",
            'brand' => ['@type' => 'Brand', 'name' => config('ulbrich.name')],
            'manufacturer' => ['@id' => self::businessId()],
            'image' => PageMeta::absolute('/images/fotos/klappmesser-offen-1200.jpg'),
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $e['price'],
                'priceCurrency' => 'EUR',
                'url' => PageMeta::absolute($e['url']),
                'seller' => ['@id' => self::businessId()],
            ],
        ]);
    }

    /**
     * Ratgeber-Artikel. Kein Datum, solange keins bekannt ist (nichts erfinden).
     *
     * @return array<string, mixed>
     */
    public static function article(string $headline, string $url, string $description): array
    {
        return [
            '@type' => 'Article',
            'headline' => $headline,
            'description' => $description,
            'url' => $url,
            'inLanguage' => 'de-DE',
            'author' => ['@id' => self::personId()],
            'publisher' => ['@id' => self::businessId()],
            'image' => PageMeta::absolute(config('seo.default_og_image')),
        ];
    }

    /** @return array<string, mixed> */
    public static function service(string $name, string $description, string $url): array
    {
        return [
            '@type' => 'Service',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'serviceType' => $name,
            'provider' => ['@id' => self::businessId()],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>|null>  $nodes
     */
    public static function graph(array $nodes): string
    {
        return json_encode(
            ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($nodes))],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_PRETTY_PRINT
        );
    }

    /**
     * Entfernt null-Werte rekursiv.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::clean($value);
            }
            if ($value === null) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
