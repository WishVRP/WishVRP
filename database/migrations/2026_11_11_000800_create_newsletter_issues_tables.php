<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Newsletter-Ausgaben, Versandprotokoll je Empfänger und persönliche Rabattcodes. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_issues', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 10)->default('manuell');      // auto | manuell
            $table->string('week', 10)->nullable()->unique();     // ISO-Woche der Auto-Ausgabe, z. B. 2026-W47
            $table->string('subject', 190);
            $table->string('preheader', 190)->nullable();
            $table->text('body')->nullable();                     // Markdown (manuell) bzw. leer (auto: aus Daten)
            $table->json('blocks')->nullable();                   // Momentaufnahme der Auto-Inhalte
            $table->string('status', 12)->default('entwurf');     // entwurf | geplant | sendet | versendet
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->unsignedSmallInteger('discount_valid_days')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->unsignedTinyInteger('percent');
            $table->string('email', 190);
            $table->string('email_key', 190)->index();
            $table->string('source', 20);                          // newsletter
            $table->foreignId('newsletter_issue_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('valid_until');
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('newsletter_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email', 190);
            $table->string('status', 12);                           // versendet | fehler
            $table->string('error', 255)->nullable();
            $table->foreignId('discount_code_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['newsletter_issue_id', 'newsletter_subscriber_id'], 'nl_deliveries_issue_subscriber_unique');   // kurzer Name: MySQL erlaubt max. 64 Zeichen
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_deliveries');
        Schema::dropIfExists('discount_codes');
        Schema::dropIfExists('newsletter_issues');
    }
};
