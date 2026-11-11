<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Edition: „offen“ heißt jetzt „frei“ (Nummer wählbar). Laut Inhaber sind 19 Stück verkauft –
 * welche Nummern, ist offen; vorläufig Nr. 1–19 (in der Verwaltung korrigierbar).
 * Weihnachtsaktion: eine Teilnahme je E-Mail, Rabattcode einmalig.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('edition_pieces')->where('status', 'offen')->update(['status' => 'frei']);
        DB::table('edition_pieces')->whereBetween('number', [1, 19])->where('status', 'frei')->update([
            'status' => 'vergeben',
            'note' => 'Vor der neuen Website verkauft (laut Inhaber 19 Stück) – Nummer prüfen',
        ]);

        Schema::create('xmas_entries', function (Blueprint $table) {
            $table->id();
            $table->string('email', 190);
            $table->string('email_key', 190)->unique();     // normalisiert: klein, ohne +Zusatz
            $table->string('token', 64)->unique();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedSmallInteger('score')->nullable();
            $table->unsignedTinyInteger('percent')->nullable();
            $table->string('code', 20)->nullable()->unique();
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('discount_code', 20)->nullable()->after('amount');
            $table->unsignedTinyInteger('discount_percent')->nullable()->after('discount_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['discount_code', 'discount_percent']));
        Schema::dropIfExists('xmas_entries');
        DB::table('edition_pieces')->where('status', 'frei')->update(['status' => 'offen']);
    }
};
