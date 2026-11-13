<?php

namespace App\Support;

/** Ob eine Seite aus config/pages.php indexiert wird – einige erst, wenn genug echter Inhalt da ist. */
final class PageIndex
{
    public const GALLERY_MIN = 6;

    public static function indexable(array $page): bool
    {
        if (($page['index_when'] ?? null) === 'gallery') {
            try {
                return \App\Models\GalleryItem::query()->visible()->count() >= self::GALLERY_MIN;
            } catch (\Throwable) {
                return false;
            }
        }

        return $page['index'] ?? true;
    }
}
