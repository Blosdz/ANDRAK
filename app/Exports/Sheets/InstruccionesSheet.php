<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InstruccionesSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    public function title(): string
    {
        return 'Instrucciones';
    }

    public function array(): array
    {
        return [
            ['Como importar productos'],
            [''],
            ['1. Completa la hoja "Productos" — una fila por producto.'],
            ['2. Las columnas en rojo (nombre, precio) son obligatorias; el resto son opcionales.'],
            ['3. codigo: se usa para buscar el producto. Escribelo como una etiqueta, ej. #excavadoras o #maquinaria_pesada.'],
            ['   Si no empieza con #, se agrega automaticamente. Debe ser unico por producto.'],
            ['4. moneda acepta PEN o USD (por defecto PEN si se deja vacio).'],
            ['5. estado acepta activo o inactivo (por defecto activo si se deja vacio).'],
            ['6. stock_actual y stock_minimo son numeros enteros (por defecto 0 y 5).'],
            ['7. imagen_base64 es opcional: pega el contenido en base64 de la imagen (con o sin el prefijo data:image/...;base64,).'],
            ['8. Guarda el archivo y subelo desde Productos > Importar Excel.'],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 100];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '001B3D']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FBAE17']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                foreach (range(3, 10) as $row) {
                    $sheet->getStyle("A{$row}")->getFont()->setSize(11);
                }
            },
        ];
    }
}
