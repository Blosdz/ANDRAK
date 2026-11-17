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
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->nullable()->constrained('ventas');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('tipo_comprobante_id', 2);
            $table->foreign('tipo_comprobante_id')->references('id')->on('tipos_comprobante');
            $table->char('serie', 4);
            $table->unsignedInteger('numero');
            $table->string('moneda_id', 3)->default('PEN');
            $table->foreign('moneda_id')->references('id')->on('tipos_moneda');
            $table->date('fecha_emision');
            $table->time('hora_emision');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('estado', 20)->default('pendiente');
            $table->string('hash')->nullable();
            $table->text('qr')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('cdr_path')->nullable();
            $table->json('respuesta_sunat')->nullable();
            $table->boolean('has_cdr')->default(false);
            $table->timestamps();

            $table->unique(['tipo_comprobante_id', 'serie', 'numero']);
        });

        Schema::create('comprobante_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->string('descripcion');
            $table->string('unidad_medida_id', 3)->default('NIU');
            $table->foreign('unidad_medida_id')->references('id')->on('unidades_medida');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->string('tipo_afectacion_igv_id', 2)->default('10');
            $table->foreign('tipo_afectacion_igv_id')->references('id')->on('tipos_afectacion_igv');
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_items');
        Schema::dropIfExists('comprobantes');
    }
};
