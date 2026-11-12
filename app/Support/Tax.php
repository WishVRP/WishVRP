<?php

namespace App\Support;

/** Steuer nach config('ulbrich.tax_mode'): kleinunternehmer (§ 19 UStG) | regular (19 % inkl.) | pending. */
final class Tax
{
    public const RATE = 19;

    public static function mode(): string
    {
        return (string) config('ulbrich.tax_mode', 'pending');
    }

    /** Enthaltene Umsatzsteuer eines Bruttobetrags (nur bei regulärer Besteuerung). */
    public static function included(float $gross): ?float
    {
        return self::mode() === 'regular' ? round($gross * self::RATE / (100 + self::RATE), 2) : null;
    }

    public static function note(): ?string
    {
        return match (self::mode()) {
            'kleinunternehmer' => 'Gemäß § 19 UStG wird keine Umsatzsteuer berechnet.',
            'regular' => 'Alle Preise inkl. '.self::RATE.' % MwSt.',
            default => null,
        };
    }
}
