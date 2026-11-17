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
        Schema::table('productos', function (Blueprint $table) {
            $table->string('codigo')->nullable()->after('nombre');
            $table->text('descripcion')->nullable()->after('codigo');
            $table->string('categoria')->nullable()->after('descripcion');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo')->after('img');
            $table->softDeletes();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->longText('img')->nullable()->change();
        });

        DB::statement('CREATE UNIQUE INDEX productos_codigo_unique_active ON productos (codigo) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS productos_codigo_unique_active');

        Schema::table('productos', function (Blueprint $table) {
            $table->string('img')->nullable()->change();
            $table->dropColumn(['codigo', 'descripcion', 'categoria', 'estado']);
            $table->dropSoftDeletes();
        });
    }
    /*
     * RUC 20 EMPRESA RUC 10
     * */
};
