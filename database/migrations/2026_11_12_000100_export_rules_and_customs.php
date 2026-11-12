<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ausfuhr/Einfuhr: Regeln je Zielland und Messerart (in der Verwaltung gepflegt, nach rechtlicher Prüfung),
     * protokollierte gesperrte Bestellversuche, Zoll-Einstellungen je Ländergruppe.
     */
    public function up(): void
    {
        Schema::create('export_rules', function (Blueprint $table) {
            $table->id();
            $table->string('country', 2)->default('*');        // ISO-Code oder * (alle Länder)
            $table->string('knife_type', 30)->default('*');    // Messerart oder * (alle)
            $table->string('action', 10);                      // erlaubt | pruefen | gesperrt
            $table->string('message', 300)->nullable();        // Text für Kunden
            $table->string('reason', 500)->nullable();         // interner Grund
            $table->string('source', 300)->nullable();         // Quelle (Anwalt, Zoll, Gesetz)
            $table->date('checked_on')->nullable();
            $table->timestamps();
            $table->unique(['country', 'knife_type']);
        });
        Schema::create('blocked_checkouts', function (Blueprint $table) {
            $table->id();
            $table->string('country', 2);
            $table->string('knife_type', 30)->nullable();
            $table->string('item', 190)->nullable();
            $table->string('action', 10);
            $table->string('reason', 500)->nullable();
            $table->foreignId('export_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email', 190)->nullable();
            $table->string('name', 120)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('source_path', 190)->nullable();
            $table->timestamps();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('knife_type', 30)->nullable();
        });
        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->string('customs_mode', 12)->default('empfaenger'); // empfaenger (DAP) | vorab (DDP)
            $table->decimal('duty_percent', 5, 2)->nullable();          // Zollsatz in %
            $table->decimal('vat_percent', 5, 2)->nullable();           // Einfuhrumsatzsteuer in %
            $table->decimal('customs_fee', 8, 2)->nullable();           // Pauschale Zollabwicklung (€)
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('customs_amount', 8, 2)->nullable();
            $table->string('export_note', 500)->nullable();
            $table->string('knife_type', 30)->nullable();
        });

        // Startregeln: nur gesicherte Grundlagen. Alles andere entscheidet der Inhaber nach rechtlicher Prüfung.
        $now = now();
        DB::table('export_rules')->insert([
            ['country' => 'DE', 'knife_type' => '*', 'action' => 'erlaubt', 'message' => null, 'reason' => 'Inland: Versand aus der eigenen Werkstatt', 'source' => 'Betrieb', 'checked_on' => null, 'created_at' => $now, 'updated_at' => $now],
            ['country' => '*', 'knife_type' => 'kein_messer', 'action' => 'erlaubt', 'message' => null, 'reason' => 'Kein Messer (z. B. Ringe, Zubehör)', 'source' => 'Betrieb', 'checked_on' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['customs_amount', 'export_note', 'knife_type']));
        Schema::table('shipping_zones', fn (Blueprint $t) => $t->dropColumn(['customs_mode', 'duty_percent', 'vat_percent', 'customs_fee']));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('knife_type'));
        Schema::dropIfExists('blocked_checkouts');
        Schema::dropIfExists('export_rules');
    }
};
