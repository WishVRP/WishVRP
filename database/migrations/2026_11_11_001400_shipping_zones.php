<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Versandkosten nach Land und Gewicht (DHL-Preise, in der Verwaltung pflegbar).
     * Angezeigt wird immer aufgerundet auf ,99 (App\Support\Shipping::roundUp).
     */
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->json('countries')->nullable();          // ISO-Codes, z. B. ["AT","FR"]
            $table->boolean('rest_of_world')->default(false); // gilt für alle Länder ohne eigene Zone
            $table->boolean('customs')->default(false);       // Zoll/Einfuhrabgaben möglich (außerhalb EU)
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('max_weight_g');           // bis einschließlich
            $table->decimal('dhl_price', 8, 2);                // Preis laut DHL (vor Aufrundung)
            $table->string('label', 80)->nullable();           // z. B. „DHL Paket bis 5 kg“
            $table->timestamps();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('weight_g')->nullable();   // Versandgewicht inkl. Verpackung
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable();
            $table->string('shipping_note', 190)->nullable();
        });

        // Startwerte: Zonen anlegen. Deutschland: Paket bis 5 kg (7,69 € → 7,99 € wie bisher festgelegt) und bis 10 kg.
        // Die günstigere 2-kg-Stufe (6,19 €) lässt sich in der Verwaltung ergänzen.
        // Preise nur für Deutschland (DHL Privatkunden-Tarif 2026 laut
        // Vergleichsportalen; vor dem Start mit dhl.de bzw. dem eigenen DHL-Vertrag abgleichen).
        // Ausland bewusst ohne Preise → „Versandkosten auf Anfrage“, bis echte Preise eingetragen sind.
        $now = now();
        $zones = [
            ['Deutschland', ['DE'], false, false, [[5000, 7.69, 'DHL Paket bis 5 kg'], [10000, 10.49, 'DHL Paket bis 10 kg']]],
            ['EU', ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'], false, false, []],
            ['Europa (außerhalb der EU)', ['CH', 'LI', 'NO', 'IS', 'GB', 'AD', 'MC', 'SM', 'VA', 'AL', 'BA', 'ME', 'MK', 'RS', 'MD', 'UA', 'TR'], false, true, []],
            ['Welt', [], true, true, []],
        ];
        foreach ($zones as $i => [$name, $countries, $rest, $customs, $rates]) {
            $id = DB::table('shipping_zones')->insertGetId([
                'name' => $name, 'countries' => json_encode($countries), 'rest_of_world' => $rest, 'customs' => $customs,
                'active' => true, 'sort' => $i, 'created_at' => $now, 'updated_at' => $now,
            ]);
            foreach ($rates as [$g, $price, $label]) {
                DB::table('shipping_rates')->insert(['shipping_zone_id' => $id, 'max_weight_g' => $g, 'dhl_price' => $price, 'label' => $label, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['country_code', 'shipping_note']));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('weight_g'));
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
    }
};
