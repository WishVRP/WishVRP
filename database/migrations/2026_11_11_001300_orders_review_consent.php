<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Einwilligung in die Bitte um eine Bewertung per E-Mail (BGH VI ZR 225/17: ohne Einwilligung = Werbung).
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('review_consent')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('review_consent');
        });
    }
};
