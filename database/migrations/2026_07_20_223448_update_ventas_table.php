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
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('numero')->nullable()->unique()->after('id');
            $table->enum('moneda', ['USD', 'PEN'])->default('PEN')->after('monto_final');
            $table->unique('cotizacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropUnique(['cotizacion_id']);
            $table->dropColumn(['numero', 'moneda']);
        });
    }
};
