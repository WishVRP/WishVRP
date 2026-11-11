<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Verwaltung: Bestellungen/Buchungen, interne Notizen, datensparsame Statistik
 * (keine IP-Adressen, keine User-Agents, keine Cookies – nur Zählungen).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();
            $table->string('type', 20)->index();      // kurs | gutschein | messer | service | anfrage
            $table->string('status', 20)->default('neu')->index();
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('phone', 40)->nullable();
            $table->string('street', 160)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('country', 80)->nullable();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_date_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('participants')->nullable();
            $table->string('item', 190)->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('amount', 10, 2)->nullable();   // Richtbetrag laut Preis × Menge, ohne Versand
            $table->string('preferred_date', 120)->nullable();
            $table->text('message')->nullable();
            $table->string('source_path', 190)->nullable();
            $table->boolean('seats_counted')->default(false);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('done_at')->nullable();
            $table->timestamps();
        });

        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path', 190)->index();
            $table->string('source', 20)->index();    // intern | direkt | suche | instagram | facebook | ki | andere
            $table->string('referrer_host', 120)->nullable();
            $table->string('device', 10);             // mobil | tablet | desktop
            $table->boolean('is_entry')->default(false);
            $table->date('viewed_on')->index();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('site_events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->index();      // telefon | email | buchen | gutschein | extern | anfrage | buchung
            $table->string('path', 190)->nullable();
            $table->string('target', 190)->nullable();
            $table->date('occurred_on')->index();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_events');
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('orders');
    }
};
