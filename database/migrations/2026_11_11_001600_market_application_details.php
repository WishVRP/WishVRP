<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Angaben laut Standmietvertrag: Biertische (innen), Lebensmittel/Getränke, Vorführung
    public function up(): void
    {
        Schema::table('market_applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('tables')->nullable();
            $table->boolean('food')->default(false);
            $table->boolean('demo')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('market_applications', fn (Blueprint $t) => $t->dropColumn(['tables', 'food', 'demo']));
    }
};
