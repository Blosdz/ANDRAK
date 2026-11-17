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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('tipo_pago_id')->nullable()->constrained('tipos_de_pago');
            $table->decimal('monto_final', 12, 2);
            $table->unsignedInteger('cuotas')->default(1);
            $table->string('url_pago')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
