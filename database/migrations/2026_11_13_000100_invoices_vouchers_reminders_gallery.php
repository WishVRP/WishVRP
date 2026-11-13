<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rechnungen (fortlaufende Nummer, Storno), Gutschein-Versand an die beschenkte Person zum Wunschtermin,
 * Kurs-Erinnerung (einmalig) und Galerie „Bei uns geschmiedet“.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->string('invoice_number', 20)->nullable()->unique();
            $t->date('invoice_date')->nullable();
            $t->string('cancel_invoice_number', 20)->nullable()->unique();
            $t->date('cancel_invoice_date')->nullable();
            $t->timestamp('reminder_sent_at')->nullable();
        });

        Schema::table('vouchers', function (Blueprint $t) {
            $t->string('recipient_email', 190)->nullable();
            $t->date('send_on')->nullable();
            $t->text('gift_message')->nullable();
            $t->timestamp('sent_to_recipient_at')->nullable();
        });

        Schema::create('invoice_counters', function (Blueprint $t) {
            $t->unsignedSmallInteger('year')->primary();
            $t->unsignedInteger('last')->default(0);
        });

        Schema::create('gallery_items', function (Blueprint $t) {
            $t->id();
            $t->string('image', 255);
            $t->string('alt', 190);
            $t->string('caption', 190)->nullable();
            $t->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('consent')->default(false);   // Einwilligung der abgebildeten Person liegt vor
            $t->boolean('published')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('invoice_counters');
        Schema::table('vouchers', fn (Blueprint $t) => $t->dropColumn(['recipient_email', 'send_on', 'gift_message', 'sent_to_recipient_at']));
        Schema::table('orders', function (Blueprint $t) {
            $t->dropUnique(['invoice_number']);
            $t->dropUnique(['cancel_invoice_number']);
            $t->dropColumn(['invoice_number', 'invoice_date', 'cancel_invoice_number', 'cancel_invoice_date', 'reminder_sent_at']);
        });
    }
};
