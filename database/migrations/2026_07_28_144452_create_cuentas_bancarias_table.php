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
        Schema::create('cuentas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresa')->cascadeOnDelete();
            $table->string('banco');
            $table->string('moneda', 10);
            $table->string('numero_cuenta');
            $table->string('cci')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        $empresa = DB::table('empresa')->first();

        if ($empresa) {
            $ahora = now();

            if (! empty($empresa->cuenta_soles)) {
                DB::table('cuentas_bancarias')->insert([
                    'empresa_id' => $empresa->id,
                    'banco' => $empresa->banco_nombre ?: 'BANCARIA',
                    'moneda' => 'PEN',
                    'numero_cuenta' => $empresa->cuenta_soles,
                    'cci' => $empresa->cci_soles,
                    'orden' => 0,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }

            if (! empty($empresa->cuenta_dolares)) {
                DB::table('cuentas_bancarias')->insert([
                    'empresa_id' => $empresa->id,
                    'banco' => $empresa->banco_nombre ?: 'BANCARIA',
                    'moneda' => 'USD',
                    'numero_cuenta' => $empresa->cuenta_dolares,
                    'cci' => $empresa->cci_dolares,
                    'orden' => 1,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }
        }

        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn(['banco_nombre', 'cuenta_soles', 'cci_soles', 'cuenta_dolares', 'cci_dolares']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->string('banco_nombre')->nullable()->after('representante_dni');
            $table->string('cuenta_soles')->nullable()->after('banco_nombre');
            $table->string('cci_soles')->nullable()->after('cuenta_soles');
            $table->string('cuenta_dolares')->nullable()->after('cci_soles');
            $table->string('cci_dolares')->nullable()->after('cuenta_dolares');
        });

        $soles = DB::table('cuentas_bancarias')->where('moneda', 'PEN')->orderBy('orden')->first();
        $dolares = DB::table('cuentas_bancarias')->where('moneda', 'USD')->orderBy('orden')->first();

        if ($soles || $dolares) {
            DB::table('empresa')->where('id', ($soles ?? $dolares)->empresa_id)->update([
                'banco_nombre' => optional($soles ?? $dolares)->banco,
                'cuenta_soles' => optional($soles)->numero_cuenta,
                'cci_soles' => optional($soles)->cci,
                'cuenta_dolares' => optional($dolares)->numero_cuenta,
                'cci_dolares' => optional($dolares)->cci,
            ]);
        }

        Schema::dropIfExists('cuentas_bancarias');
    }
};
