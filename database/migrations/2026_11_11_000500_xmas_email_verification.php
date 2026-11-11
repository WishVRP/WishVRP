<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Weihnachtsaktion: Spiel erst nach bestätigter E-Mail (persönlicher Link). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xmas_entries', function (Blueprint $table) {
            $table->string('verify_token', 48)->nullable()->unique()->after('token');
            $table->timestamp('verified_at')->nullable()->after('verify_token');
            $table->timestamp('started_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('xmas_entries', function (Blueprint $table) {
            $table->dropColumn(['verify_token', 'verified_at']);
        });
    }
};
