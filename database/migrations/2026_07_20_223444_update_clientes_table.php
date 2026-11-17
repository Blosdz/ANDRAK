<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->renameColumn('telefono_contacto', 'telefono');
            $table->renameColumn('nombre_contacto', 'contacto');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->string('correo')->nullable()->after('nombre');
            $table->string('empresa')->nullable()->after('correo');
            $table->softDeletes();
            $table->dropUnique('clientes_ruc_unique');
        });

        DB::statement('CREATE UNIQUE INDEX clientes_ruc_unique_active ON clientes (ruc) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS clientes_ruc_unique_active');

        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('ruc');
            $table->dropColumn(['correo', 'empresa']);
            $table->dropSoftDeletes();
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->renameColumn('telefono', 'telefono_contacto');
            $table->renameColumn('contacto', 'nombre_contacto');
        });
    }
};
