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
        Schema::table('almacen', function (Blueprint $table) {
            $table->unsignedInteger('stock_minimo')->default(5)->after('cantidad_disponible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('almacen', function (Blueprint $table) {
            $table->dropColumn('stock_minimo');
        });
    }
};
