<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('referencia')->nullable()->after('detalles');
            $table->unsignedInteger('validez_dias')->nullable()->default(5)->after('referencia');
            $table->string('garantia')->nullable()->after('validez_dias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['referencia', 'validez_dias', 'garantia']);
        });
    }
};
