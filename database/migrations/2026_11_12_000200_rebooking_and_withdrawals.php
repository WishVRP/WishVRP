<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** Kurs umbuchen (2× kostenlos) per persönlichem Link; elektronische Widerrufsfunktion. */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('manage_token', 48)->nullable()->unique();
            $table->unsignedTinyInteger('rebook_count')->default(0);
            $table->timestamp('rebooked_at')->nullable();
            $table->date('original_course_start')->nullable();
        });
        foreach (DB::table('orders')->whereNull('manage_token')->pluck('id') as $id) {
            DB::table('orders')->where('id', $id)->update(['manage_token' => Str::random(48)]);
        }
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number', 20)->nullable();      // wie vom Kunden angegeben
            $table->string('name', 120);
            $table->string('email', 190);
            $table->text('message')->nullable();                 // freiwillig (Grund muss nicht angegeben werden)
            $table->string('status', 12)->default('eingegangen'); // eingegangen | angenommen | abgelehnt
            $table->text('decision_note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['manage_token', 'rebook_count', 'rebooked_at', 'original_course_start']));
    }
};
