<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Widerruf: optional betroffene Artikel/Positionen (Teilwiderruf). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $t) {
            $t->text('items')->nullable()->after('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', fn (Blueprint $t) => $t->dropColumn('items'));
    }
};
