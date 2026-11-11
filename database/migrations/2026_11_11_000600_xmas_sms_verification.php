<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Weihnachtsaktion: Bestätigung per SMS-Code – eine Handynummer = eine Teilnahme. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xmas_entries', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->after('email_key');
            $table->string('phone_key', 20)->nullable()->unique()->after('phone');   // E.164, z. B. +491711234567
            $table->string('sms_code_hash', 255)->nullable()->after('verified_at');
            $table->timestamp('sms_sent_at')->nullable()->after('sms_code_hash');
            $table->unsignedTinyInteger('sms_count')->default(0)->after('sms_sent_at');
            $table->unsignedTinyInteger('sms_attempts')->default(0)->after('sms_count');
        });
    }

    public function down(): void
    {
        Schema::table('xmas_entries', fn (Blueprint $table) => $table->dropColumn(['phone', 'phone_key', 'sms_code_hash', 'sms_sent_at', 'sms_count', 'sms_attempts']));
    }
};
