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
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_comprobante_id', 2);
            $table->foreign('tipo_comprobante_id')->references('id')->on('tipos_comprobante');
            $table->char('serie', 4);
            $table->unsignedInteger('correlativo')->default(0);
            $table->timestamps();

            $table->unique('tipo_comprobante_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
