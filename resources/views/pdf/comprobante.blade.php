@php
    $tipoLabel = $comprobante->tipo_comprobante_id === '01' ? 'FACTURA ELECTRONICA' : 'BOLETA DE VENTA ELECTRONICA';
    $qrDataUri = null;
    if ($comprobante->qr) {
        $qrResult = (new \Endroid\QrCode\Writer\PngWriter())->write(
            new \Endroid\QrCode\QrCode(data: $comprobante->qr, size: 120, margin: 4)
        );
        $qrDataUri = $qrResult->getDataUri();
    }

    // Sistema de Detracciones (SPOT): aplica sobre servicios cuyo monto en
    // soles supera S/ 700, sin importar si el cliente tiene RUC o DNI. La
    // tasa varia segun el tipo de servicio (Anexo 3 SUNAT); 12% es la tasa
    // mas comun y se usa aqui como referencia informativa.
    $totalEnSoles = $comprobante->moneda_id === 'USD' ? $comprobante->total_pen : $comprobante->total;
    $porcentajeDetraccion = 0.12;
    $montoDetraccion = ($totalEnSoles && $totalEnSoles > 700)
        ? round($totalEnSoles * $porcentajeDetraccion, 2)
        : null;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #131b2e; }
        table { width: 100%; border-collapse: collapse; }
        .text-right { text-align: right; }
        .text-muted { color: #6b7280; }
        .mt-16 { margin-top: 16px; }
        .mt-24 { margin-top: 24px; }
        .numero-box { border: 1px solid #131b2e; padding: 10px; text-align: center; }
        .estado-aceptado { color: #15803d; }
        .estado-rechazado { color: #b91c1c; }
        .estado-observado { color: #b45309; }
        .estado-pendiente { color: #6b7280; }
        .detraccion-box { border: 1px solid #d8dde6; background-color: #f5f6fa; padding: 8px 10px; font-size: 10px; }
    </style>
</head>
<body>
    @include('pdf.partials.header', ['empresa' => $empresa])

    <table class="mt-24">
        <tr>
            <td width="60%">
                <strong>Cliente</strong><br>
                {{ $comprobante->cliente->nombre }}<br>
                {{ $comprobante->cliente->tipo_documento_id === '6' ? 'RUC' : 'DNI' }}: {{ $comprobante->cliente->ruc }}<br>
                Fecha de emision: {{ $comprobante->fecha_emision->format('d/m/Y') }}
            </td>
            <td width="40%">
                <div class="numero-box">
                    <strong>{{ $tipoLabel }}</strong><br>
                    <span style="font-size: 16px;">{{ $comprobante->serie }}-{{ str_pad($comprobante->numero, 8, '0', STR_PAD_LEFT) }}</span><br>
                    <span class="estado-{{ $comprobante->estado }}">{{ strtoupper($comprobante->estado) }}</span>
                </div>
            </td>
        </tr>
    </table>

    @include('pdf.partials.detalle-table', ['detalles' => $comprobante->items, 'moneda' => $comprobante->moneda_id])

    @include('pdf.partials.totales', [
        'subtotal' => $comprobante->subtotal,
        'impuestos' => $comprobante->total_igv,
        'total' => $comprobante->total,
        'moneda' => $comprobante->moneda_id,
        'tipoCambio' => $comprobante->tipo_cambio,
        'totalPen' => $comprobante->total_pen,
    ])

    @if ($montoDetraccion)
        <table class="mt-16">
            <tr>
                <td class="detraccion-box">
                    <strong>Operacion sujeta a Sistema de Detracciones (SPOT).</strong>
                    Monto de la detraccion ({{ number_format($porcentajeDetraccion * 100, 0) }}%): S/ {{ number_format($montoDetraccion, 2) }}.
                    La tasa aplicable puede variar segun el tipo de servicio (Anexo 3 de la R.S. 183-2004/SUNAT); verificar antes del deposito en el Banco de la Nacion.
                </td>
            </tr>
        </table>
    @endif

    <table class="mt-24">
        <tr>
            <td width="70%" style="font-size: 10px;">
                @if ($comprobante->hash)
                    <p><strong>Codigo hash:</strong> {{ $comprobante->hash }}</p>
                @endif
                <p class="text-muted">Representacion impresa de un comprobante de pago electronico.</p>
                <p class="text-muted">Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
            </td>
            <td width="30%" class="text-right">
                @if ($qrDataUri)
                    <img src="{{ $qrDataUri }}" style="width: 100px; height: 100px;">
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
