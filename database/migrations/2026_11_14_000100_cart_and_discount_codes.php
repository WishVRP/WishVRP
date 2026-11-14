<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Warenkorb (mehrere Artikel je Bestellung) und Rabattcodes aus der Verwaltung:
 * Prozent oder Euro, mehrfach nutzbar, Bereich (Shop/Kurse/Gutscheine), optional an eine E-Mail gebunden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->unsignedTinyInteger('percent')->nullable()->change();
            $table->string('email', 190)->nullable()->change();
            $table->string('email_key', 190)->nullable()->change();
            $table->timestamp('valid_until')->nullable()->change();
            $table->string('type', 10)->default('percent')->after('code');      // percent | amount
            $table->decimal('amount', 10, 2)->nullable()->after('percent');      // Euro-Rabatt
            $table->decimal('min_order', 10, 2)->nullable()->after('amount');
            $table->string('scope', 20)->default('alles')->after('min_order');  // alles | shop | kurse | gutscheine
            $table->unsignedInteger('max_uses')->nullable()->default(1)->after('scope');   // null = unbegrenzt
            $table->unsignedInteger('uses')->default(0)->after('max_uses');
            $table->timestamp('valid_from')->nullable()->after('newsletter_issue_id');
            $table->string('label', 120)->nullable()->after('source');           // Anlass, z. B. „Gewinnspiel Instagram“
            $table->timestamp('sent_at')->nullable()->after('redeemed_at');
        });
        DB::table('discount_codes')->whereNotNull('redeemed_at')->update(['uses' => 1]);

        Schema::table('orders', function (Blueprint $table) {
            $table->json('items')->nullable()->after('item');                    // Warenkorb: [{product_id, name, qty, price, url}]
            $table->decimal('discount_amount', 10, 2)->nullable()->after('discount_percent');
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['items', 'discount_amount']));
        Schema::table('discount_codes', fn (Blueprint $table) => $table->dropColumn(['type', 'amount', 'min_order', 'scope', 'max_uses', 'uses', 'valid_from', 'label', 'sent_at']));
    }
};
