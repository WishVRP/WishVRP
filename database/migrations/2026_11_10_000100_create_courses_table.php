<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Bestehender Pfad der Live-Kursseite, z. B. /schmiedekurse/<slug>/ – 1:1 übernehmen.
            $table->string('path')->nullable()->unique();
            // Wert für /schmiedekurse/buchen/?kurs=… (muss zur Altseite passen).
            $table->string('booking_key')->nullable();
            $table->string('duration_label')->nullable();
            $table->unsignedTinyInteger('blade_length_cm')->nullable();
            // Ganze Euro, „ab“-Preis pro Person. null = auf Anfrage.
            $table->unsignedInteger('price_from')->nullable();
            $table->text('summary')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('course_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->unsignedTinyInteger('seats_total');
            $table->unsignedTinyInteger('seats_booked')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['starts_on', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_dates');
        Schema::dropIfExists('courses');
    }
};
