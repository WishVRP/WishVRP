<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Shipping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Live-Versandkosten fürs Bestellformular (Land/PLZ/Ort → Preis). Der Server rechnet beim Absenden noch einmal selbst. */
class ShippingController extends Controller
{
    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'land' => ['required', 'string', 'size:2'],
            'plz' => ['nullable', 'string', 'max:20'],
            'ort' => ['nullable', 'string', 'max:120'],
            'produkt' => ['nullable', 'integer'],
            'edition' => ['nullable', 'boolean'],
        ]);
        $product = ! empty($data['produkt']) ? Product::query()->find($data['produkt']) : null;
        $q = Shipping::quote($data['land'], $product?->weight_g, $data['plz'] ?? null, $data['ort'] ?? null);
        $type = $product ? $product->knife_type : (! empty($data['edition']) ? config('ulbrich.export.edition_type') : null);
        $export = ($product || ! empty($data['edition'])) ? \App\Support\ExportCheck::evaluate($data['land'], $type) : ['action' => 'erlaubt', 'message' => null];
        $value = $product ? (float) $product->price : (! empty($data['edition']) ? (float) config('ulbrich.edition.price') : 0.0);
        $customs = \App\Support\Customs::estimate($data['land'], $value, (float) ($q['price'] ?? 0));

        return response()->json($q + [
            'formatted' => $q['price'] !== null ? number_format($q['price'], 2, ',', '.').' €' : null,
            'note' => $q['price'] === null
                ? 'Für '.$q['country_name'].' nennen wir dir die Versandkosten nach deiner Anfrage per E-Mail – erst dann zahlst du.'
                : ($customs['mode'] === 'empfaenger' ? $customs['label'] : null),
            'export' => ['action' => $export['action'], 'message' => $export['message']],
            'customs_amount' => $customs['amount'],
            'customs_formatted' => $customs['amount'] ? number_format($customs['amount'], 2, ',', '.').' €' : null,
            'customs_label' => $customs['mode'] === 'vorab' ? $customs['label'] : null,
        ])->header('Cache-Control', 'no-store');
    }
}
