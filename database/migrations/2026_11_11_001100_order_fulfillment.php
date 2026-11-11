<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ablauf nach dem Kauf: In Arbeit → Versendet (Tracking) → Zugestellt, dazu Bewertungsanfrage
        Schema::table('orders', function (Blueprint $table) {
            $table->string('production_time', 80)->nullable();     // z. B. „ca. 3–4 Wochen“
            $table->timestamp('in_progress_at')->nullable();
            $table->string('tracking_carrier', 20)->nullable();    // dhl, dpd, hermes, gls, ups, post, sonstige
            $table->string('tracking_number', 60)->nullable();
            $table->string('tracking_url', 500)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('tracking_checked_at')->nullable();
            $table->timestamp('review_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['production_time', 'in_progress_at', 'tracking_carrier', 'tracking_number', 'tracking_url', 'delivered_at', 'tracking_checked_at', 'review_requested_at']);
        });
    }
};
