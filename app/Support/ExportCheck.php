<?php

namespace App\Support;

use App\Models\BlockedCheckout;
use App\Models\ExportRule;
use App\Models\Order;

/**
 * Darf diese Messerart in dieses Land verschickt werden?
 * Reihenfolge: Land + Messerart → Land + alle → alle + Messerart → Standard (config: prüfen).
 * Die Regeln sind in der Verwaltung gepflegt; diese Klasse erfindet kein Recht.
 */
final class ExportCheck
{
    /** @return array{action: string, message: ?string, reason: string, rule_id: ?int, hs: ?string, type_label: string} */
    public static function evaluate(string $country, ?string $knifeType): array
    {
        $country = strtoupper($country ?: 'DE');
        $type = $knifeType ?: 'sonstiges';
        $types = config('ulbrich.export.knife_types');
        $rule = ExportRule::query()
            ->whereIn('country', [$country, '*'])->whereIn('knife_type', [$type, '*'])
            ->get()
            ->sortBy(fn ($r) => ($r->country === '*' ? 2 : 0) + ($r->knife_type === '*' ? 1 : 0))
            ->first();
        $action = $rule->action ?? config('ulbrich.export.default_action', 'pruefen');

        return [
            'action' => $action,
            'message' => $action === 'erlaubt' ? null : ($rule?->message ?: config('ulbrich.export.messages.'.$action)),
            'reason' => $rule?->reason ?: ($rule ? 'Regel ohne Begründung' : 'Keine Regel für '.Shipping::countryName($country).' / '.($types[$type]['label'] ?? $type).' hinterlegt – Standard: prüfen'),
            'rule_id' => $rule?->id,
            'hs' => $types[$type]['hs'] ?? null,
            'type_label' => $types[$type]['label'] ?? $type,
        ];
    }

    /** Gesperrten Versuch für die Verwaltung festhalten. */
    public static function logBlocked(array $check, string $country, ?string $knifeType, array $context): void
    {
        BlockedCheckout::query()->create([
            'country' => strtoupper($country), 'knife_type' => $knifeType, 'item' => $context['item'] ?? null,
            'action' => $check['action'], 'reason' => $check['reason'], 'export_rule_id' => $check['rule_id'],
            'email' => $context['email'] ?? null, 'name' => $context['name'] ?? null,
            'postal_code' => $context['postal_code'] ?? null, 'city' => $context['city'] ?? null,
            'source_path' => $context['source_path'] ?? null,
        ]);
    }

    /** Hinweis für die Bestellung (pruefen) bzw. Versandpapiere (außerhalb EU). */
    public static function note(array $check, bool $customs): ?string
    {
        $parts = [];
        if ($check['action'] === 'pruefen') {
            $parts[] = 'Einfuhr prüfen: '.$check['reason'];
        }
        if ($customs) {
            $parts[] = 'Außerhalb der EU: Zollinhaltserklärung (CN22/CN23) bzw. Handelsrechnung beilegen'.($check['hs'] ? ', Zolltarifnummer '.$check['hs'] : '').' – '.$check['type_label'];
        }

        return $parts ? implode(' · ', $parts) : null;
    }
}
