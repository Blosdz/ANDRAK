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
            $table->dropForeign(['producto_id']);
            $table->dropColumn(['producto_id', 'monto_cotizado']);
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreignId('cliente_id')->after('id')->constrained('clientes');
            $table->string('numero')->nullable()->unique()->after('cliente_id');
            $table->enum('estado', ['borrador', 'enviada', 'aceptada', 'rechazada', 'convertida'])
                ->default('borrador')->after('numero');
            $table->enum('moneda', ['USD', 'PEN'])->default('PEN')->after('estado');
            $table->decimal('subtotal', 12, 2)->default(0)->after('moneda');
            $table->decimal('impuestos', 12, 2)->nullable()->after('subtotal');
            $table->decimal('total', 12, 2)->default(0)->after('impuestos');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn(['cliente_id', 'numero', 'estado', 'moneda', 'subtotal', 'impuestos', 'total']);
            $table->dropSoftDeletes();
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('monto_cotizado', 12, 2)->default(0);
        });
    }
};
