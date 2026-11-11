<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Edition 100: eine Zeile je Seriennummer. Käufername erscheint öffentlich nur mit Zustimmung (show_name).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edition_pieces', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('number')->unique();
            $table->string('status', 20)->default('offen');   // offen | reserviert | vergeben
            $table->string('buyer_name', 80)->nullable();      // Anzeigename, z. B. „Erika M.“
            $table->boolean('show_name')->default(false);      // Zustimmung zur Veröffentlichung
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note', 190)->nullable();
            $table->timestamps();
        });

        $now = now();
        $rows = [];
        for ($i = 1; $i <= (int) config('ulbrich.edition.total', 100); $i++) {
            $rows[] = ['number' => $i, 'status' => 'offen', 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('edition_pieces')->insert($rows);

        Schema::table('orders', function (Blueprint $table) {
            $table->string('public_name', 80)->nullable()->after('message');   // Anzeigename im Edition-Raster (mit Zustimmung)
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('public_name'));
        Schema::dropIfExists('edition_pieces');
    }
};
