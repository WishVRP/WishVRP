<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kursdetails von der Live-Seite (11.11.2026).
 * blade_length_cm → total_length_cm: Die Live-Seite nennt ausdrücklich die GESAMTlänge inkl. Griff.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('blade_length_cm', 'total_length_cm');
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('hours_label')->nullable()->after('duration_label');
            $table->string('project')->nullable()->after('hours_label');
            $table->json('inclusions')->nullable()->after('project');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['slug', 'hours_label', 'project', 'inclusions']);
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('total_length_cm', 'blade_length_cm');
        });
    }
};
