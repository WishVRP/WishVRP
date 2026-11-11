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
        ]);
        $weight = ! empty($data['produkt']) ? Product::query()->whereKey($data['produkt'])->value('weight_g') : null;
        $q = Shipping::quote($data['land'], $weight, $data['plz'] ?? null, $data['ort'] ?? null);

        return response()->json($q + [
            'formatted' => $q['price'] !== null ? number_format($q['price'], 2, ',', '.').' €' : null,
            'note' => $q['price'] === null
                ? 'Für '.$q['country_name'].' nennen wir dir die Versandkosten nach deiner Anfrage per E-Mail – erst dann zahlst du.'
                : ($q['customs'] ? 'Zoll und Einfuhrabgaben im Zielland trägt der Empfänger.' : null),
        ])->header('Cache-Control', 'no-store');
    }
}
