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
        Schema::table('empresa', function (Blueprint $table) {
            $table->string('tipo_documento_id', 1)->default('6')->after('ruc');
            $table->string('razon_social')->nullable()->after('nombre_comercial');
            $table->string('establecimiento_codigo', 4)->default('0000')->after('direccion');
            $table->string('ubigeo', 6)->nullable()->after('direccion');
            $table->string('modo_sunat', 10)->default('beta')->after('telefono');
            $table->text('sunat_usuario_sol')->nullable()->after('modo_sunat');
            $table->text('sunat_clave_sol')->nullable()->after('sunat_usuario_sol');
            $table->string('certificado')->nullable()->after('sunat_clave_sol');
            $table->date('certificado_vencimiento')->nullable()->after('certificado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_documento_id',
                'razon_social',
                'establecimiento_codigo',
                'ubigeo',
                'modo_sunat',
                'sunat_usuario_sol',
                'sunat_clave_sol',
                'certificado',
                'certificado_vencimiento',
            ]);
        });
    }
};
