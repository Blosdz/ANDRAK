<?php

namespace App\Exports\Sheets;

use App\Exports\ProductosTemplateExport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProductosTemplateSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithTitle
{
    /** Colores de marca — navy/oro de Industrial Integrity, ver resources/css/app.css. */
    private const COLOR_REQUERIDO_FILL = 'FFDAD6';
    private const COLOR_REQUERIDO_TEXTO = '93000A';
    private const COLOR_OPCIONAL_FILL = 'E7EEFF';
    private const COLOR_OPCIONAL_TEXTO = '001B3D';

    public function title(): string
    {
        return 'Productos';
    }

    public function headings(): array
    {
        return array_values(ProductosTemplateExport::COLUMNAS);
    }

    public function array(): array
    {
        return [
            [
                'Excavadora hidraulica CX-350', '#excavadoras', 'Excavadora de orugas 35 ton', 'Maquinaria pesada',
                185000, 'PEN', 'activo', 4, 1, '9.7x2.98x3.15 m', '35200 kg', 'Diesel 268 HP', '',
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, 'B' => 22, 'C' => 34, 'D' => 20, 'E' => 12, 'F' => 14,
            'G' => 18, 'H' => 12, 'I' => 12, 'J' => 18, 'K' => 14, 'L' => 20, 'M' => 40,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $columnas = array_keys(ProductosTemplateExport::COLUMNAS);

                foreach ($columnas as $i => $key) {
                    $col = chr(65 + $i);
                    $esRequerido = in_array($key, ProductosTemplateExport::REQUERIDOS, true);

                    $sheet->getStyle("{$col}1")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => $esRequerido ? self::COLOR_REQUERIDO_TEXTO : self::COLOR_OPCIONAL_TEXTO],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => $esRequerido ? self::COLOR_REQUERIDO_FILL : self::COLOR_OPCIONAL_FILL],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);
                }

                $sheet->getStyle('A2:M2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '74777F']],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->freezePane('A2');
            },
        ];
    }
}
