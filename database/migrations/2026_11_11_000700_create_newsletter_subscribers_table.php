<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Newsletter mit Double-Opt-in: Werbung nur an bestätigte Adressen (§ 7 UWG, Art. 6/7 DSGVO).
 * Nachweis der Einwilligung: Zeitpunkt, Quelle, Wortlaut, Bestätigung.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 190);
            $table->string('email_key', 190)->unique();
            $table->string('source', 40);                    // z. B. weihnachtsaktion
            $table->text('consent_text');                    // Wortlaut der Einwilligung
            $table->timestamp('consented_at');
            $table->string('confirm_token', 48)->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('unsubscribe_token', 48)->unique();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
