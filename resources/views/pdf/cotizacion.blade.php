@php
    $fecha = $cotizacion->created_at->format('d/m/Y');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 26px 30px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #131b2e; }
        table { width: 100%; border-collapse: collapse; }
        .text-right { text-align: right; }
        .text-muted { color: #6b7280; }
        .mt-16 { margin-top: 16px; }
        .mt-20 { margin-top: 20px; }
        .section-title { color: #0b1f3a; font-size: 13px; font-weight: bold; }
        .datos-table td { border: 1px solid #d8dde6; padding: 8px 10px; font-size: 10.5px; vertical-align: top; }
        .datos-table td strong { color: #0b1f3a; }
        .items-table th { background-color: #0b1f3a; color: #ffffff; padding: 7px; font-size: 10px; text-align: left; }
        .items-table td { border: 1px solid #d8dde6; padding: 7px; font-size: 10.5px; vertical-align: top; }
        .totales-table td { border: 1px solid #d8dde6; padding: 7px 10px; font-size: 10.5px; }
        .totales-table .total-row td { background-color: #0b1f3a; color: #ffffff; font-weight: bold; font-size: 12px; }
        ul.obs { margin: 6px 0 0 0; padding-left: 16px; font-size: 10.5px; }
        ul.obs li { margin-bottom: 3px; }
        ul.specs { margin: 0; padding-left: 14px; font-size: 9.5px; }
        ul.specs li { margin-bottom: 2px; }
        .detraccion-box { border: 1px solid #d8dde6; background-color: #f5f6fa; padding: 8px 10px; font-size: 10px; }
    </style>
</head>
<body>
    @include('pdf.partials.watermark', ['empresa' => $empresa])

    @include('pdf.partials.brand-header', [
        'empresa' => $empresa,
        'titulo' => 'COTIZACION',
        'numero' => $cotizacion->numero,
        'fecha' => $fecha,
    ])

    @include('pdf.partials.cotizacion-cuerpo', ['cotizacion' => $cotizacion, 'equivalentePen' => $equivalentePen])

    @include('pdf.partials.brand-footer', ['empresa' => $empresa])
</body>
</html>
