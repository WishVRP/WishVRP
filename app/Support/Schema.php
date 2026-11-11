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

    /** Verweis auf das Unternehmen mit Name/URL – lesbar auch, wenn der LocalBusiness-Knoten nicht im Graph steht. */
    public static function businessRef(): array
    {
        return ['@type' => 'LocalBusiness', '@id' => self::businessId(), 'name' => config('ulbrich.name'), 'url' => PageMeta::absolute('/')];
    }

    /** @return array<string, string> */
    public static function personRef(): array
    {
        return ['@type' => 'Person', '@id' => self::personId(), 'name' => config('ulbrich.owner')];
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
            'foundingDate' => config('ulbrich.heritage.opened_on'),
            'disambiguatingDescription' => "Messerschmiede mit Schmiedekursen in {$c['address']['district']}, {$c['address']['locality']} (Landkreis Heilbronn, Baden-Württemberg), Inhaber {$c['owner']}.",
            'description' => "Schmiedekurse und handgefertigte Messer von {$c['owner_role']} {$c['owner']} – Schmiedehandwerk seit {$c['heritage']['since']} in der Familie, heute in {$c['heritage']['generation']}. Generation.",
            'knowsAbout' => ['Messerschmieden', 'Damaststahl', 'Klappmesser', 'Schmiedekurse', 'Küchenmesser', 'Jagdmesser', 'Messer schärfen', 'Messerreparatur', 'Gravur'],
            'areaServed' => $c['sales']['area_served'] ?? null,
            'currenciesAccepted' => 'EUR',
            'paymentAccepted' => implode(', ', [...$c['sales']['payment'], ...array_map(fn ($p) => $p.' (vor Ort)', $c['sales']['payment_onsite'])]),
            'makesOffer' => array_map(fn ($s) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $s]], $c['sales']['services']),
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
            'image' => PageMeta::absolute(Foto::url('andreas-esse', 1200)),
            'worksFor' => ['@id' => self::businessId()],
            'memberOf' => ['@type' => 'Organization', 'name' => $c['chamber']],
            'description' => "{$c['owner_role']} in {$c['heritage']['generation']}. Generation – Schmiedehandwerk seit {$c['heritage']['since']} in der Familie.",
            'knowsAbout' => ['Messerschmieden', 'Damaststahl', 'Klappmesser', 'Schmiedekurse'],
            'areaServed' => $c['sales']['area_served'] ?? null,
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
            'inLanguage' => ['de-DE', 'en'],
            'publisher' => ['@id' => self::businessId()],
        ];
    }

    /** @return array<string, mixed> */
    public static function webPage(PageMeta $meta): array
    {
        $profile = in_array($meta->path, ['/manufaktur/', '/en/about/'], true);

        return self::clean([
            '@type' => $profile ? 'ProfilePage' : 'WebPage',
            'mainEntity' => $profile ? ['@id' => self::personId()] : null,
            '@id' => $meta->canonical().'#webpage',
            'url' => $meta->canonical(),
            'name' => $meta->fullTitle(),
            'description' => $meta->description,
            'inLanguage' => $meta->locale() === 'en' ? 'en' : 'de-DE',
            'isPartOf' => ['@id' => PageMeta::absolute('/').'#website'],
            'about' => self::businessRef(),
        ]);
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
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => Inline::plain(strip_tags($f['a']))],
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
                    'url' => PageMeta::absolute($course->url()),
                    'provider' => self::businessRef(),
                ]),
            ])->all(),
        ];
    }

    /**
     * Einzelner Kurs (nur sichtbare Angaben). Angebot mit Preis „ab“ als Offer; ohne Termine kein CourseInstance.
     *
     * @return array<string, mixed>
     */
    public static function course(\App\Models\Course $course): array
    {
        return self::clean([
            '@type' => 'Course',
            'name' => $course->name,
            'description' => $course->project.' – '.$course->duration_label.($course->hours_label ? ', '.$course->hours_label : ''),
            'url' => PageMeta::absolute($course->url()),
            'provider' => self::businessRef(),
            'inLanguage' => 'de-DE',
            'offers' => $course->price_from ? [
                '@type' => 'Offer',
                'price' => (string) $course->price_from,
                'priceCurrency' => 'EUR',
                'category' => 'Paid',
                'description' => 'ab-Preis pro Person',
                'url' => PageMeta::absolute($course->bookingUrl()),
            ] : null,
            'hasCourseInstance' => self::courseInstances($course),
        ]);
    }

    /**
     * Kursdurchführungen: je veröffentlichtem Termin eine Instanz mit Datum; ohne Termine
     * eine Instanz mit Ort und Dauer (nur wenn die Dauer eindeutig ist, z. B. „2 Tage“).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function courseInstances(\App\Models\Course $course): array
    {
        $base = ['@type' => 'CourseInstance', 'courseMode' => 'Onsite', 'location' => self::place(), 'instructor' => self::personRef()];
        $dates = $course->dates()->upcoming()->get();
        if ($dates->isNotEmpty()) {
            return $dates->map(fn ($d) => self::clean($base + [
                'startDate' => $d->starts_on->toDateString(),
                'endDate' => ($d->ends_on ?? $d->starts_on)->toDateString(),
                'courseWorkload' => 'P'.(($d->ends_on ?? $d->starts_on)->diffInDays($d->starts_on, true) + 1).'D',
            ]))->values()->all();
        }
        $days = preg_match('/^(\d+) Tage?$/u', (string) $course->duration_label, $m) ? (int) $m[1] : null;

        return [self::clean($base + ['courseWorkload' => $days ? "P{$days}D" : null])];
    }

    /**
     * Freie, veröffentlichte Kurstermine als Veranstaltung (Event-Suchergebnisse).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function courseEvents(\App\Models\Course $course): array
    {
        return $course->dates()->upcoming()->get()->map(fn ($d) => self::clean([
            '@type' => 'EducationEvent',
            'name' => $course->name.' – Schmiedekurs bei '.config('ulbrich.name'),
            'description' => $course->project.' – '.$course->duration_label,
            'startDate' => $d->starts_on->toDateString(),
            'endDate' => ($d->ends_on ?? $d->starts_on)->toDateString(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => self::place(),
            'image' => PageMeta::absolute(config('seo.default_og_image')),
            'organizer' => self::businessRef(),
            'performer' => self::personRef(),
            'offers' => $course->price_from ? [
                '@type' => 'Offer',
                'price' => (string) $course->price_from,
                'priceCurrency' => 'EUR',
                'availability' => $d->seatsLeft() > 0 ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
                'url' => PageMeta::absolute($course->bookingUrl()),
            ] : null,
        ]))->values()->all();
    }

    /** Werkstatt als Ort (Adresse aus der Konfiguration). */
    public static function place(): array
    {
        $c = config('ulbrich');

        return [
            '@type' => 'Place',
            'name' => $c['name'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $c['address']['street'],
                'postalCode' => $c['address']['postal_code'],
                'addressLocality' => $c['address']['locality'],
                'addressRegion' => $c['address']['region'],
                'addressCountry' => $c['address']['country'],
            ],
        ];
    }

    /**
     * Edition 100 als Product. Verfügbarkeit ist nicht bekannt → wird weggelassen.
     *
     * @param  array<string, mixed>  $e
     * @return array<string, mixed>
     */
    public static function product(array $e, bool $english = false): array
    {
        return self::clean([
            '@type' => 'Product',
            'name' => $e['name'],
            'description' => $english
                ? "Damascus folding knife, limited to {$e['total']} numbered pieces ({$e['regular']} regular pieces, {$e['special']} special pieces), handmade by ".config('ulbrich.owner').'.'
                : "Damast-Klappmesser, limitiert auf {$e['total']} nummerierte Stücke ({$e['regular']} reguläre Stücke, {$e['special']} Sonderstücke).",
            'material' => $english ? 'Damascus steel' : 'Damaststahl',
            'brand' => ['@type' => 'Brand', 'name' => config('ulbrich.name')],
            'manufacturer' => self::businessRef(),
            'image' => PageMeta::absolute(Foto::url('klappmesser-offen', 1200)),
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $e['price'],
                'priceCurrency' => 'EUR',
                'url' => PageMeta::absolute($e['url']),
                'seller' => self::businessRef(),
                'itemCondition' => 'https://schema.org/NewCondition',
                'availableDeliveryMethod' => array_values(array_filter([
                    'http://purl.org/goodrelations/v1#DeliveryModeParcelService',
                    config('ulbrich.sales.pickup') ? 'http://purl.org/goodrelations/v1#DeliveryModePickUp' : null,
                ])),
                'warranty' => config('ulbrich.warranty.years') ? [
                    '@type' => 'WarrantyPromise',
                    'durationOfWarranty' => ['@type' => 'QuantitativeValue', 'value' => config('ulbrich.warranty.years'), 'unitCode' => 'ANN'],
                ] : null,
                // Nur Deutschland: Auslandskosten trägt der Kunde und werden einzeln genannt.
                'shippingDetails' => config('ulbrich.sales.shipping_de_eur') ? [
                    '@type' => 'OfferShippingDetails',
                    'shippingRate' => ['@type' => 'MonetaryAmount', 'value' => (string) config('ulbrich.sales.shipping_de_eur'), 'currency' => 'EUR'],
                    'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'DE'],
                ] : null,
            ],
        ]);
    }

    /** Shop-Produkt mit Angebot (Preis, Verfügbarkeit, Versand DE, Zustand). */
    public static function shopProduct(\App\Models\Product $p): array
    {
        return self::clean([
            '@type' => 'Product',
            '@id' => PageMeta::absolute($p->url()).'#product',
            'name' => $p->name,
            'description' => $p->short ?: \Illuminate\Support\Str::limit(strip_tags(\Illuminate\Support\Str::markdown((string) $p->description)), 300),
            'sku' => 'ULB-'.$p->id,
            'category' => $p->categoryName(),
            'image' => array_map(fn ($u) => PageMeta::absolute($u), $p->imageUrls()) ?: null,
            'brand' => ['@type' => 'Brand', 'name' => config('ulbrich.name')],
            'manufacturer' => self::businessRef(),
            'additionalProperty' => collect($p->specs ?? [])->map(fn ($s) => ['@type' => 'PropertyValue', 'name' => $s['label'], 'value' => $s['value']])->values()->all() ?: null,
            'offers' => [
                '@type' => 'Offer',
                'url' => PageMeta::absolute($p->url()),
                'price' => number_format((float) $p->price, 2, '.', ''),
                'priceCurrency' => 'EUR',
                'availability' => $p->available() ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => self::businessRef(),
                'availableDeliveryMethod' => ['http://purl.org/goodrelations/v1#DeliveryModeParcelService', 'http://purl.org/goodrelations/v1#DeliveryModePickUp'],
                'shippingDetails' => [
                    '@type' => 'OfferShippingDetails',
                    'shippingRate' => ['@type' => 'MonetaryAmount', 'value' => (string) config('ulbrich.sales.shipping_de_eur'), 'currency' => 'EUR'],
                    'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'DE'],
                ],
                'warranty' => ['@type' => 'WarrantyPromise', 'durationOfWarranty' => ['@type' => 'QuantitativeValue', 'value' => config('ulbrich.warranty.years'), 'unitCode' => 'ANN']],
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
            // Autor = Manufaktur (keine erfundene Einzelautorschaft)
            'author' => self::businessRef(),
            'publisher' => self::businessRef() + ['logo' => ['@type' => 'ImageObject', 'url' => PageMeta::absolute('/images/icon-512.png')]],
            'mainEntityOfPage' => ['@id' => $url.'#webpage'],
            'about' => self::businessRef(),
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
            'provider' => self::businessRef(),
            'areaServed' => config('ulbrich.sales.area_served'),
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
