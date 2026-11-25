<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable();
        });
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->dropUnique(['tipo_comprobante_id', 'serie', 'numero']);
            $table->unique(['empresa_id', 'tipo_comprobante_id', 'serie', 'numero'], 'comprobantes_emisor_numero_unique');
        });
        Schema::table('series', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->dropUnique(['tipo_comprobante_id']);
            $table->unique(['empresa_id', 'tipo_comprobante_id'], 'series_emisor_tipo_unique');
        });

        if (DB::table('empresa')->where('id', 1)->exists()) {
            DB::table('cotizaciones')->whereNull('empresa_id')->update(['empresa_id' => 1]);
            DB::table('comprobantes')->whereNull('empresa_id')->update(['empresa_id' => 1]);
            DB::table('series')->whereNull('empresa_id')->update(['empresa_id' => 1]);
        }
    }

    public function down(): void
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropUnique('comprobantes_emisor_numero_unique');
            $table->unique(['tipo_comprobante_id', 'serie', 'numero']);
            $table->dropColumn('empresa_id');
        });
        Schema::table('series', function (Blueprint $table) {
            $table->dropUnique('series_emisor_tipo_unique');
            $table->unique('tipo_comprobante_id');
            $table->dropColumn('empresa_id');
        });
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn('empresa_id');
        });
    }
};
