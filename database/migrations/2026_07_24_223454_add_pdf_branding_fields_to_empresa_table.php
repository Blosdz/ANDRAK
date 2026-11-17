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
            $table->string('eslogan')->nullable()->after('nombre_comercial');
            $table->string('whatsapp')->nullable()->after('telefono');
            $table->string('correo')->nullable()->after('whatsapp');
            $table->string('representante_nombre')->nullable()->after('correo');
            $table->string('representante_dni')->nullable()->after('representante_nombre');
            $table->string('banco_nombre')->nullable()->after('representante_dni');
            $table->string('cuenta_soles')->nullable()->after('banco_nombre');
            $table->string('cci_soles')->nullable()->after('cuenta_soles');
            $table->string('cuenta_dolares')->nullable()->after('cci_soles');
            $table->string('cci_dolares')->nullable()->after('cuenta_dolares');
            $table->string('titular_cuenta')->nullable()->after('cci_dolares');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn([
                'eslogan',
                'whatsapp',
                'correo',
                'representante_nombre',
                'representante_dni',
                'banco_nombre',
                'cuenta_soles',
                'cci_soles',
                'cuenta_dolares',
                'cci_dolares',
                'titular_cuenta',
            ]);
        });
    }
};
