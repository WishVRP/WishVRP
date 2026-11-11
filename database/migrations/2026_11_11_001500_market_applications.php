<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Kunst- & Handwerkermarkt: Online-Standplan, Bewerbungen, kurzzeitige Reservierungen beim Ausfüllen. */
    public function up(): void
    {
        Schema::create('market_applications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();            // Aussteller-Code, z. B. KHM-7Q4XK2
            $table->string('season', 20);                    // z. B. 2027 – mehrere Märkte nacheinander
            $table->unsignedTinyInteger('stand');            // Standnummer laut Plan
            $table->string('status', 16)->default('eingegangen'); // eingegangen | angenommen | bezahlt | abgelehnt | storniert
            $table->string('name', 120);
            $table->string('company', 120)->nullable();      // Werkstatt/Marke
            $table->string('email', 190);
            $table->string('phone', 40)->nullable();
            $table->string('street', 160)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('category', 60);
            $table->text('description');                     // Sortiment, Herstellung
            $table->string('website', 190)->nullable();      // Website oder Instagram
            $table->boolean('lighting')->default(false);     // Beleuchtung gewünscht
            $table->json('photos')->nullable();              // Pfade auf der privaten Festplatte
            $table->decimal('amount', 8, 2);                 // Standgebühr + Werbepauschale (+ Beleuchtung)
            $table->text('internal_note')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['season', 'stand']);
        });
        Schema::create('market_holds', function (Blueprint $table) {
            $table->id();
            $table->string('season', 20);
            $table->unsignedTinyInteger('stand');
            $table->string('token', 64);                     // Hash der Besucher-Sitzung
            $table->timestamp('until');
            $table->timestamps();
            $table->unique(['season', 'stand']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_holds');
        Schema::dropIfExists('market_applications');
    }
};
