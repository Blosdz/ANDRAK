<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SunatCatalogSeeder extends Seeder
{
    /**
     * Seed the SUNAT catalogs and initial series.
     */
    public function run(): void
    {
        DB::table('tipos_comprobante')->upsert([
            ['id' => '01', 'descripcion' => 'Factura'],
            ['id' => '03', 'descripcion' => 'Boleta de Venta'],
            ['id' => '07', 'descripcion' => 'Nota de Crédito'],
            ['id' => '08', 'descripcion' => 'Nota de Débito'],
        ], ['id'], ['descripcion']);

        DB::table('tipos_documento_identidad')->upsert([
            ['id' => '0', 'descripcion' => 'Sin documento'],
            ['id' => '1', 'descripcion' => 'DNI'],
            ['id' => '6', 'descripcion' => 'RUC'],
        ], ['id'], ['descripcion']);

        DB::table('tipos_moneda')->upsert([
            ['id' => 'PEN', 'descripcion' => 'Soles'],
            ['id' => 'USD', 'descripcion' => 'Dólares Americanos'],
        ], ['id'], ['descripcion']);

        DB::table('tipos_afectacion_igv')->upsert([
            ['id' => '10', 'descripcion' => 'Gravado - Operación Onerosa', 'porcentaje' => 18],
            ['id' => '20', 'descripcion' => 'Exonerado - Operación Onerosa', 'porcentaje' => 0],
            ['id' => '30', 'descripcion' => 'Inafecto - Operación Onerosa', 'porcentaje' => 0],
        ], ['id'], ['descripcion', 'porcentaje']);

        DB::table('unidades_medida')->upsert([
            ['id' => 'NIU', 'descripcion' => 'Unidad'],
            ['id' => 'ZZ', 'descripcion' => 'Servicio'],
        ], ['id'], ['descripcion']);

        DB::table('series')->upsert([
            ['tipo_comprobante_id' => '03', 'serie' => 'B001', 'correlativo' => 0],
            ['tipo_comprobante_id' => '01', 'serie' => 'F001', 'correlativo' => 0],
        ], ['tipo_comprobante_id'], ['serie']);
    }
}
