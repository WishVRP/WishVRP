<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Metadaten einer Seite. Wird im Controller erzeugt und an das Layout
 * übergeben – Title, Description, Canonical, Robots, OG/Twitter und
 * Breadcrumb kommen so immer aus genau einer Quelle.
 */
final class PageMeta
{
    /**
     * @param  array<int, array{name: string, path: string}>  $breadcrumb
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $path,
        public readonly bool $index = true,
        public readonly string $ogType = 'website',
        public readonly ?string $ogImage = null,
        public readonly array $breadcrumb = [],
    ) {
        if (! str_starts_with($path, '/') || ($path !== '/' && ! str_ends_with($path, '/'))) {
            throw new InvalidArgumentException("Pfad muss mit / beginnen und enden: {$path}");
        }
    }

    public function fullTitle(): string
    {
        $suffix = config('seo.title_suffix');

        return str_ends_with($this->title, trim($suffix, ' |')) ? $this->title : $this->title.$suffix;
    }

    public function canonical(): string
    {
        return self::absolute($this->path);
    }

    public function robots(): string
    {
        return ($this->index && config('seo.indexable')) ? 'index, follow, max-image-preview:large' : 'noindex, nofollow';
    }

    public function ogImage(): string
    {
        return self::absolute($this->ogImage ?? config('seo.default_og_image'));
    }

    /** Sprache der Seite: alles unter /en/ ist Englisch, sonst Deutsch. */
    public function locale(): string
    {
        return str_starts_with($this->path, '/en/') ? 'en' : 'de';
    }

    /**
     * Sprachversionen derselben Seite (hreflang). Englische Seiten nennen ihr
     * deutsches Gegenstück in config/pages.php unter 'alternate'.
     *
     * @return array<string, string> Sprache => Pfad
     */
    public function alternates(): array
    {
        $pages = config('pages');
        if ($this->locale() === 'en') {
            $de = $pages[$this->path]['alternate'] ?? null;

            return $de ? ['de' => $de, 'en' => $this->path] : [];
        }
        foreach ($pages as $p => $page) {
            if (str_starts_with($p, '/en/') && ($page['alternate'] ?? null) === $this->path) {
                return ['de' => $this->path, 'en' => $p];
            }
        }

        return [];
    }

    /** Absolute URL auf der kanonischen Domain, Schrägstrich bleibt erhalten. */
    public static function absolute(string $path): string
    {
        return rtrim(config('seo.canonical_host'), '/').$path;
    }
}
