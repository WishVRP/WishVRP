<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bewertungen: nur wörtlich aus dem Google-Profil (oder einer anderen echten Quelle), in der Verwaltung gepflegt.
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('author', 80);              // Vorname + Initial, wie im Google-Profil angezeigt
            $table->unsignedTinyInteger('rating');     // 1–5 Sterne
            $table->text('text');
            $table->date('reviewed_on');
            $table->string('source', 40)->default('Google');
            $table->string('topic', 80)->nullable();   // z. B. Damastkurs, Klappmesser (optional)
            $table->boolean('published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        // Einfache Einstellungen (z. B. Google-Gesamtbewertung), in der Verwaltung änderbar.
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 80)->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('settings');
    }
};
