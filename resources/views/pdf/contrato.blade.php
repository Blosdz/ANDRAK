@php
    $cotizacion = $venta->cotizacion;
    $cliente = $venta->cliente;
    $simbolo = $venta->moneda === 'USD' ? 'US$' : 'S/';
    $fecha = $venta->created_at->format('d/m/Y');
    $fechaCotizacion = optional(optional($cotizacion)->created_at)->format('d/m/Y') ?? $fecha;
    $tipoDocCliente = $cliente->tipo_documento_id === '6' ? 'RUC' : 'DNI';

    $condicionPago = optional($venta->tipoPago)->descripcion
        ?: optional($venta->tipoPago)->nombre
        ?: optional($cotizacion)->tipoPago?->descripcion
        ?: 'el 100% al momento de la aceptacion de la cotizacion';

    if (optional($cotizacion)->horas_entrega) {
        $tiempoEntrega = $cotizacion->horas_entrega % 24 === 0
            ? ($cotizacion->horas_entrega / 24).' dias'
            : $cotizacion->horas_entrega.' horas';
    } else {
        $tiempoEntrega = 'a convenir entre las partes';
    }

    $garantia = optional($cotizacion)->garantia ?: '6 meses por defectos de fabricacion';

    $cuentasBancarias = $empresa->cuentasBancarias;
    $tieneCuentas = $cuentasBancarias->isNotEmpty();

    $pagos = $venta->pagos;
    $tienePagos = $pagos->isNotEmpty();
    $incluirCotizacion = $incluirCotizacion ?? true;
    $incluirContrato = $incluirContrato ?? true;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 26px 30px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10.5px; color: #131b2e; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; }
        .text-right { text-align: right; }
        .text-muted { color: #6b7280; }
        .mt-16 { margin-top: 16px; }
        .mt-20 { margin-top: 20px; }
        .page-break { page-break-before: always; }
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
        .clausula { margin-top: 14px; }
        .clausula-num {
            display: inline-block; width: 16px; height: 16px; background-color: #0b1f3a; color: #ffffff;
            text-align: center; font-size: 10px; font-weight: bold; line-height: 16px;
        }
        .clausula-titulo { color: #0b1f3a; font-size: 12px; font-weight: bold; }
        .clausula-body { margin-top: 4px; padding-left: 22px; font-size: 10.5px; }
        .clausula-body ul { margin: 4px 0 0 0; padding-left: 16px; }
        .clausula-body li { margin-bottom: 3px; }
        .cuentas-box { border: 1px solid #d8dde6; padding: 10px; margin-top: 8px; }
        .cuentas-box td { font-size: 10px; padding: 2px 6px; vertical-align: top; }
        .firma-linea { border-top: 1px solid #131b2e; margin-top: 46px; padding-top: 4px; text-align: center; font-size: 10px; }
        .cliente-box { border: 1px solid #d8dde6; padding: 10px 12px; font-size: 10.5px; }
        .cliente-box strong { color: #0b1f3a; }
        .resumen-table td { border: 1px solid #d8dde6; padding: 7px 10px; font-size: 10.5px; }
        .resumen-table .saldo-row td { color: #b3261e; font-weight: bold; }
        .resumen-table .total-row td { background-color: #0b1f3a; color: #ffffff; font-weight: bold; }
    </style>
</head>
<body>
    @include('pdf.partials.watermark', ['empresa' => $empresa])

    @if ($incluirCotizacion)
        {{-- SECCION 1: COTIZACION --}}
        @include('pdf.partials.brand-header', [
            'empresa' => $empresa,
            'titulo' => 'COTIZACION',
            'numero' => optional($cotizacion)->numero ?? $venta->numero,
            'fecha' => $fechaCotizacion,
        ])

        @if ($cotizacion)
            @include('pdf.partials.cotizacion-cuerpo', ['cotizacion' => $cotizacion])
        @endif

        @include('pdf.partials.brand-footer', ['empresa' => $empresa])

        <div class="page-break"></div>
    @endif

    @if ($incluirContrato)
    {{-- SECCION 2: CONTRATO DE COMPRA --}}
    @include('pdf.partials.brand-header', [
        'empresa' => $empresa,
        'titulo' => 'CONTRATO DE COMPRA',
        'numero' => $venta->numero,
        'fecha' => $fecha,
    ])

    <p class="mt-20">
        Conste por el presente documento, el <strong>CONTRATO DE COMPRA</strong> que celebran de una parte
        <strong>{{ $empresa->razon_social ?: $empresa->nombre_comercial }}</strong>, con RUC N&deg; {{ $empresa->ruc }},
        con domicilio legal en {{ $empresa->direccion ?: 'Peru' }}, en adelante <strong>&ldquo;EL VENDEDOR&rdquo;</strong>;
        y de la otra parte:
    </p>

    <p class="mt-16"><strong>{{ $cliente->nombre }}</strong>{{ $cliente->empresa ? ' ('.$cliente->empresa.')' : '' }}</p>

    <p>
        identificada/o con {{ $tipoDocCliente }} N&deg; {{ $cliente->ruc ?: '________________' }}, con domicilio en
        {{ $cliente->direccion ?: '________________________________' }}, en adelante <strong>&ldquo;EL COMPRADOR&rdquo;</strong>;
        en los terminos y condiciones siguientes:
    </p>

    <div class="clausula">
        <span class="clausula-num">1</span> <span class="clausula-titulo">ANTECEDENTES</span>
        <div class="clausula-body">
            EL VENDEDOR ha presentado a EL COMPRADOR la Cotizacion N&deg; {{ optional($cotizacion)->numero ?? $venta->numero }}
            de fecha {{ $fechaCotizacion }}, que forma parte
            integrante del presente contrato, y la cual ha sido aceptada por EL COMPRADOR.
        </div>
    </div>

    <div class="clausula">
        <span class="clausula-num">2</span> <span class="clausula-titulo">OBJETO DEL CONTRATO</span>
        <div class="clausula-body">
            El presente contrato tiene por objeto la compra de los siguientes productos, descritos en la Cotizacion
            N&deg; {{ optional($cotizacion)->numero ?? $venta->numero }}, la cual forma parte integrante del presente documento:
            <ul>
                @forelse (optional($cotizacion)->lineas ?? [] as $linea)
                    <li>{{ $linea->cantidad }} x {{ $linea->nombre }} &mdash; {{ $simbolo }} {{ number_format($linea->subtotal, 2) }}</li>
                @empty
                    <li>Segun el detalle de la Cotizacion N&deg; {{ $venta->numero }}.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="clausula">
        <span class="clausula-num">3</span> <span class="clausula-titulo">PRECIO Y FORMA DE PAGO</span>
        <div class="clausula-body">
            El precio total del producto, segun la cotizacion aceptada, es de: <strong>{{ $simbolo }} {{ number_format($venta->monto_final, 2) }}</strong>
            @if ($venta->tipo_cambio && $venta->monto_final_pen)
                (equivalente a <strong>S/ {{ number_format($venta->monto_final_pen, 2) }}</strong> al tipo de cambio S/ {{ number_format($venta->tipo_cambio, 3) }} por USD)
            @endif
            .<br>
            EL COMPRADOR realizara el pago mediante {{ $condicionPago }}.
            @if ($tieneCuentas)
                <br>El pago se realizara mediante deposito o transferencia bancaria a las siguientes cuentas:
                <table class="cuentas-box">
                    @foreach ($cuentasBancarias->chunk(2) as $par)
                        <tr>
                            @foreach ($par as $cuenta)
                                <td width="50%">
                                    <strong>CUENTA {{ $cuenta->banco }} {{ $cuenta->moneda === 'USD' ? 'DOLARES' : 'SOLES' }}</strong><br>
                                    Cuenta: {{ $cuenta->numero_cuenta }}<br>
                                    @if ($cuenta->cci) CCI: {{ $cuenta->cci }} @endif
                                </td>
                            @endforeach
                            @if ($par->count() === 1)
                                <td width="50%"></td>
                            @endif
                        </tr>
                    @endforeach
                    @if ($empresa->titular_cuenta)
                        <tr><td colspan="2">Titular: {{ $empresa->titular_cuenta }}</td></tr>
                    @endif
                </table>
            @endif
        </div>
    </div>

    <div class="clausula">
        <span class="clausula-num">4</span> <span class="clausula-titulo">TIEMPO DE ENTREGA</span>
        <div class="clausula-body">
            El tiempo de entrega sera de {{ $tiempoEntrega }}, contados a partir de la aceptacion del presente contrato
            y las condiciones de pago pactadas.
        </div>
    </div>

    <div class="clausula">
        <span class="clausula-num">5</span> <span class="clausula-titulo">GARANTIA</span>
        <div class="clausula-body">
            El producto cuenta con garantia de {{ $garantia }}.
        </div>
    </div>

    <div class="clausula">
        <span class="clausula-num">6</span> <span class="clausula-titulo">RESPONSABILIDADES</span>
        <div class="clausula-body">
            <ul>
                <li>EL VENDEDOR se compromete a entregar el producto en las condiciones establecidas en la cotizacion.</li>
                <li>EL COMPRADOR se compromete a realizar el pago en la forma y plazo establecidos.</li>
            </ul>
        </div>
    </div>

    <div class="clausula">
        <span class="clausula-num">7</span> <span class="clausula-titulo">ACEPTACION</span>
        <div class="clausula-body">
            Las partes manifiestan su conformidad con todos los terminos del presente contrato, firmando en senal de aceptacion.
        </div>
    </div>

    <table class="mt-20" style="margin-top: 30px;">
        <tr>
            <td width="50%" style="text-align: center; font-weight: bold; color: #0b1f3a;">EL VENDEDOR</td>
            <td width="50%" style="text-align: center; font-weight: bold; color: #0b1f3a;">EL COMPRADOR</td>
        </tr>
        <tr>
            <td width="50%">
                <div class="firma-linea">
                    {{ $empresa->razon_social ?: $empresa->nombre_comercial }}<br>
                    RUC N&deg; {{ $empresa->ruc }}<br>
                    @if ($empresa->representante_nombre) {{ $empresa->representante_nombre }}<br> @endif
                    @if ($empresa->representante_dni) DNI N&deg; {{ $empresa->representante_dni }} @endif
                </div>
            </td>
            <td width="50%">
                <div class="firma-linea">
                    {{ $cliente->nombre }}<br>
                    {{ $tipoDocCliente }} N&deg; {{ $cliente->ruc ?: '________________' }}
                </div>
            </td>
        </tr>
    </table>

    @include('pdf.partials.brand-footer', ['empresa' => $empresa])
    @endif

    @if ($tienePagos)
        {{-- SECCION 3: RECIBO DE PAGO --}}
        @if ($incluirCotizacion || $incluirContrato)
            <div class="page-break"></div>
        @endif

        @include('pdf.partials.brand-header', [
            'empresa' => $empresa,
            'titulo' => 'RECIBO DE PAGO',
            'numero' => $venta->numero,
            'fecha' => optional($pagos->last()->fecha_pago)->format('d/m/Y') ?? $fecha,
        ])

        <p class="mt-20">Por medio del presente, se deja constancia de los pagos recibidos por parte de:</p>

        <table class="cliente-box mt-16">
            <tr>
                <td>
                    <strong>CLIENTE:</strong> {{ $cliente->nombre }}<br>
                    {{ $tipoDocCliente }}: {{ $cliente->ruc ?: '-' }}
                </td>
            </tr>
        </table>

        <div class="mt-20 section-title">PRODUCTO(S)</div>
        <table class="items-table mt-16">
            <thead>
                <tr>
                    <th width="8%">ITEM</th>
                    <th width="72%">DESCRIPCION</th>
                    <th width="20%" class="text-right">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse (optional($cotizacion)->lineas ?? [] as $i => $linea)
                    <tr>
                        <td class="text-right">{{ $i + 1 }}</td>
                        <td>{{ $linea->cantidad }} x {{ $linea->nombre }}</td>
                        <td class="text-right">{{ $simbolo }} {{ number_format($linea->subtotal, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Segun el detalle de la Cotizacion N&deg; {{ $venta->numero }}.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-20 section-title">DETALLE DE PAGOS</div>
        <table class="items-table mt-16">
            <thead>
                <tr>
                    <th width="20%">FECHA</th>
                    <th width="55%">NOTA</th>
                    <th width="25%" class="text-right">MONTO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pagos as $pago)
                    <tr>
                        <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                        <td>{{ $pago->nota ?: '-' }}</td>
                        <td class="text-right">{{ $simbolo }} {{ number_format($pago->monto, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="mt-16">
            <tr>
                <td width="55%"></td>
                <td width="45%">
                    <table class="resumen-table">
                        <tr class="total-row">
                            <td>MONTO TOTAL</td>
                            <td class="text-right">{{ $simbolo }} {{ number_format($venta->monto_final, 2) }}</td>
                        </tr>
                        <tr>
                            <td>MONTO PAGADO ({{ $venta->porcentaje_pagado }}%)</td>
                            <td class="text-right">{{ $simbolo }} {{ number_format($venta->monto_pagado, 2) }}</td>
                        </tr>
                        <tr class="saldo-row">
                            <td>SALDO PENDIENTE</td>
                            <td class="text-right">{{ $simbolo }} {{ number_format($venta->saldo_pendiente, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <p class="mt-20">
            Este recibo confirma la recepcion de los pagos indicados, los cuales se destinan a la compra del producto descrito.
            Agradecemos su confianza.
        </p>

        <table class="mt-20" style="margin-top: 40px;">
            <tr>
                <td width="50%"></td>
                <td width="50%">
                    <div class="firma-linea">
                        {{ $empresa->razon_social ?: $empresa->nombre_comercial }}<br>
                        @if ($empresa->representante_nombre) {{ $empresa->representante_nombre }}<br> @endif
                        @if ($empresa->representante_dni) DNI N&deg; {{ $empresa->representante_dni }} @endif
                    </div>
                </td>
            </tr>
        </table>

        @include('pdf.partials.brand-footer', ['empresa' => $empresa])
    @endif
</body>
</html>
