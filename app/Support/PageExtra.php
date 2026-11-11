<?php

namespace App\Support;

/**
 * Ergänzende Inhalte je Seite (resources/content/extra/{pfad}.json):
 * Abschnitte und FAQ, die unter dem Seiteninhalt erscheinen. Die FAQ werden
 * mit einem vorhandenen FAQPage-Knoten zusammengeführt (eine FAQPage pro Seite).
 */
final class PageExtra
{
    public static function file(string $path): ?string
    {
        $slug = str_replace('/', '__', trim($path, '/'));
        $file = resource_path('content/extra/'.($slug === '' ? 'home' : $slug).'.json');

        return is_file($file) ? $file : null;
    }

    /** @return array<int, array{q: string, a: string}> */
    public static function faq(string $path): array
    {
        $file = self::file($path);

        return $file ? (json_decode(file_get_contents($file), true)['faq'] ?? []) : [];
    }

    /**
     * @param  array<int, array<string, mixed>|null>  $nodes
     * @return array<int, array<string, mixed>|null>
     */
    public static function mergeFaq(array $nodes, string $path): array
    {
        $faq = self::faq($path);
        if ($faq === []) {
            return $nodes;
        }
        $add = Schema::faq($faq);
        foreach ($nodes as $i => $node) {
            if (($node['@type'] ?? null) === 'FAQPage') {
                $nodes[$i]['mainEntity'] = [...$node['mainEntity'], ...$add['mainEntity']];

                return $nodes;
            }
        }
        $nodes[] = $add;

        return $nodes;
    }
}
