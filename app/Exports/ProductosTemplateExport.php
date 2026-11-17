<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductosTemplateExport implements WithMultipleSheets
{
    /** Column keys, in template order. Kept alongside the sheet class so
     * ProductosImport can reference the same required/optional split. */
    public const REQUERIDOS = ['nombre', 'precio'];

    public const COLUMNAS = [
        'nombre' => 'nombre*',
        'codigo' => 'codigo',
        'descripcion' => 'descripcion',
        'categoria' => 'categoria',
        'precio' => 'precio*',
        'moneda' => 'moneda',
        'estado' => 'estado',
        'cantidad_disponible' => 'stock_actual',
        'stock_minimo' => 'stock_minimo',
        'dimensiones' => 'dimensiones',
        'peso' => 'peso',
        'motor' => 'motor',
        'imagen_base64' => 'imagen_base64',
    ];

    public function sheets(): array
    {
        return [
            'Instrucciones' => new Sheets\InstruccionesSheet(),
            'Productos' => new Sheets\ProductosTemplateSheet(),
        ];
    }
}
