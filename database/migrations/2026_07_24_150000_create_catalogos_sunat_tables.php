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
        Schema::create('tipos_comprobante', function (Blueprint $table) {
            $table->string('id', 2)->primary();
            $table->string('descripcion');
        });

        Schema::create('tipos_documento_identidad', function (Blueprint $table) {
            $table->string('id', 1)->primary();
            $table->string('descripcion');
        });

        Schema::create('tipos_moneda', function (Blueprint $table) {
            $table->string('id', 3)->primary();
            $table->string('descripcion');
        });

        Schema::create('tipos_afectacion_igv', function (Blueprint $table) {
            $table->string('id', 2)->primary();
            $table->string('descripcion');
            $table->decimal('porcentaje', 5, 2)->default(0);
        });

        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->string('id', 3)->primary();
            $table->string('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades_medida');
        Schema::dropIfExists('tipos_afectacion_igv');
        Schema::dropIfExists('tipos_moneda');
        Schema::dropIfExists('tipos_documento_identidad');
        Schema::dropIfExists('tipos_comprobante');
    }
};
