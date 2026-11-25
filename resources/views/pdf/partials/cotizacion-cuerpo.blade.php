@php
    $simbolo = $cotizacion->moneda === 'USD' ? 'US$' : 'S/';
    $condicionPago = optional($cotizacion->tipoPago)->descripcion
        ?: optional($cotizacion->tipoPago)->nombre
        ?: 'Segun acuerdo con el cliente';
    $referencia = $cotizacion->referencia ?: 'Segun requerimiento';
    $atencion = optional($cotizacion->cliente)->contacto;
    $validez = $cotizacion->validez_dias ? "{$cotizacion->validez_dias} dias" : 'A convenir';

    if ($cotizacion->horas_entrega) {
        $tiempoEntrega = $cotizacion->horas_entrega % 24 === 0
            ? ($cotizacion->horas_entrega / 24).' dias'
            : $cotizacion->horas_entrega.' horas';
    } else {
        $tiempoEntrega = 'A convenir';
    }

    $tasaImpuesto = $cotizacion->impuestos && $cotizacion->subtotal
        ? round(($cotizacion->impuestos / $cotizacion->subtotal) * 100)
        : null;

    // Sistema de Detracciones (SPOT): aplica sobre servicios cuyo monto en
    // soles supera S/ 700. La tasa varia segun el tipo de servicio (Anexo 3
    // SUNAT); 12% es la tasa mas comun y se usa aqui como referencia informativa.
    $totalEnSolesDetraccion = $cotizacion->moneda === 'USD' ? ($equivalentePen['total'] ?? null) : $cotizacion->total;
    $porcentajeDetraccion = 0.12;
    $montoDetraccion = ($totalEnSolesDetraccion && $totalEnSolesDetraccion > 700)
        ? round($totalEnSolesDetraccion * $porcentajeDetraccion, 2)
        : null;

    $observaciones = array_values(array_filter([
        is_null($cotizacion->impuestos) || $cotizacion->impuestos == 0
            ? "Precio en {$cotizacion->moneda}: {$simbolo} ".number_format($cotizacion->subtotal, 2).' (no incluye IGV).'
            : null,
        "Tiempo de entrega: {$tiempoEntrega}.",
        "Validez de la oferta: {$validez}.",
        $cotizacion->garantia ? "Garantia: {$cotizacion->garantia}." : null,
        'La cotizacion incluye unicamente lo descrito en la presente.',
    ]));

    $notasAdicionales = $cotizacion->detalles
        ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $cotizacion->detalles))))
        : [];
    $cuentaPago = $empresa->cuentasBancarias->firstWhere('moneda', 'PEN');
@endphp
<table class="datos-table mt-16">
    <tr>
        <td width="50%"><strong>RAZÓN SOCIAL:</strong> {{ $empresa->razon_social }}</td>
        <td width="50%"><strong>RUC:</strong> {{ $empresa->ruc }}</td>
    </tr>
    <tr>
        <td colspan="2"><strong>UBICACIÓN:</strong> {{ $empresa->direccion }}</td>
    </tr>
</table>
<div class="mt-20 section-title">DATOS DE LA COTIZACION</div>
<table class="datos-table mt-16">
    <tr>
        <td width="50%"><strong>SOLICITANTE:</strong> {{ $cotizacion->cliente->nombre }}</td>
        <td width="50%"><strong>REFERENCIA:</strong> {{ $referencia }}</td>
    </tr>
    <tr>
        <td><strong>ATENCION:</strong> {{ $atencion ?: '-' }}</td>
        <td><strong>VALIDEZ DE OFERTA:</strong> {{ $validez }}</td>
    </tr>
    <tr>
        <td><strong>CONDICION DE PAGO:</strong> {{ $condicionPago }}</td>
        <td><strong>TIEMPO DE ENTREGA:</strong> {{ $tiempoEntrega }}</td>
    </tr>
</table>

<table class="items-table mt-20">
    <thead>
        <tr>
            <th width="5%">ITEM</th>
            <th width="24%">DESCRIPCION</th>
            <th width="28%">ESPECIFICACIONES</th>
            <th width="8%" class="text-right">CANT.</th>
            <th width="16%" class="text-right">PRECIO UNITARIO</th>
            <th width="19%" class="text-right">SUBTOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cotizacion->lineas as $i => $linea)
            @php
                $producto = $linea->producto;
                $specs = array_values(array_filter([
                    $producto?->descripcion_extra['dimensiones'] ?? null
                        ? 'Dimensiones: '.$producto->descripcion_extra['dimensiones']
                        : null,
                    $producto?->descripcion_extra['peso'] ?? null
                        ? 'Peso: '.$producto->descripcion_extra['peso']
                        : null,
                    $producto?->descripcion_extra['motor'] ?? null
                        ? 'Motor: '.$producto->descripcion_extra['motor']
                        : null,
                    $producto?->descripcion ?: null,
                ]));
            @endphp
            <tr>
                <td class="text-right">{{ $i + 1 }}</td>
                <td>
                    @if ($producto?->img)
                        <img src="{{ $producto->img }}" style="max-width: 70px; max-height: 60px; display: block; margin-bottom: 4px;">
                    @endif
                    {{ $linea->nombre }}
                </td>
                <td>
                    @if ($specs)
                        <ul class="specs">
                            @foreach ($specs as $spec)
                                <li>{{ $spec }}</li>
                            @endforeach
                        </ul>
                    @else
                        &mdash;
                    @endif
                </td>
                <td class="text-right">{{ $linea->cantidad }}</td>
                <td class="text-right">{{ $simbolo }} {{ number_format($linea->precio_unitario, 2) }}</td>
                <td class="text-right">{{ $simbolo }} {{ number_format($linea->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="mt-16">
    <tr>
        <td width="60%"></td>
        <td width="40%">
            <table class="totales-table">
                <tr>
                    <td>SUBTOTAL</td>
                    <td class="text-right">{{ $simbolo }} {{ number_format($cotizacion->subtotal, 2) }}</td>
                </tr>
                @if (! is_null($cotizacion->impuestos) && $cotizacion->impuestos > 0)
                    <tr>
                        <td>IGV{{ $tasaImpuesto ? " ({$tasaImpuesto}%)" : '' }}</td>
                        <td class="text-right">{{ $simbolo }} {{ number_format($cotizacion->impuestos, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="text-right">{{ $simbolo }} {{ number_format($cotizacion->total, 2) }}</td>
                </tr>
                @if ($equivalentePen)
                    <tr>
                        <td>EQUIVALENTE</td>
                        <td class="text-right">S/ {{ number_format($equivalentePen['total'], 2) }}</td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

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

<div class="mt-20 section-title">OBSERVACIONES</div>
<ul class="obs">
    @foreach ($observaciones as $obs)
        <li>{{ $obs }}</li>
    @endforeach
    @if ($equivalentePen)
        <li>Referencial: S/ {{ number_format($equivalentePen['total'], 2) }} al tipo de cambio SUNAT del {{ \Carbon\Carbon::parse($equivalentePen['fecha'])->format('d/m/Y') }} (S/ {{ number_format($equivalentePen['tipo_cambio'], 3) }} por USD). El monto a pagar en soles se fijara con el tipo de cambio vigente al momento de la venta.</li>
    @endif
    @foreach ($notasAdicionales as $nota)
        <li>{{ $nota }}</li>
    @endforeach
</ul>

@if ($cuentaPago)
    <div class="mt-20 section-title">DATOS BANCARIOS Y FORMAS DE PAGO</div>
    <table class="datos-table mt-16">
        <tr>
            <td width="50%"><strong>BANCO:</strong> {{ $cuentaPago->banco }}</td>
            <td width="50%"><strong>CUENTA EN SOLES:</strong> {{ $cuentaPago->numero_cuenta }}</td>
        </tr>
        <tr>
            <td><strong>CCI:</strong> {{ $cuentaPago->cci }}</td>
            <td><strong>TITULAR:</strong> {{ $empresa->titular_cuenta }}</td>
        </tr>
    </table>
    @if ($empresa->titular_cuenta)
        <p>Los depósitos o transferencias se realizan a nombre de {{ $empresa->titular_cuenta }}.</p>
    @endif
@endif

@if ($empresa->ruc === '20616592395')
<div class="mt-20 section-title">PERSONAL RESPONSABLE</div>
<table class="datos-table mt-16">
    <tr>
        <td width="50%">
            <strong>LIC. ANDREA MARIET LIMA ESCOBEDO</strong><br>
            Gerente General<br>
            ANDRAK S.A.C.
        </td>
        <td width="50%">
            <strong>LUIS ANTONIO LIMA YUCRA</strong><br>
            Jefe tecnico de fabricación<br>
            ANDRAK S.A.C.
        </td>
    </tr>
</table>
@elseif ($empresa->representante_nombre)
<div class="mt-20 section-title">PERSONAL RESPONSABLE</div>
<p>{{ $empresa->representante_nombre }}</p>
@endif
