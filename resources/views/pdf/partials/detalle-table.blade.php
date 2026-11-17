<table class="mt-24">
    <thead>
        <tr style="background-color: #eaedff;">
            <th style="padding: 6px; text-align: left;">Producto</th>
            <th style="padding: 6px; text-align: right;">Cantidad</th>
            <th style="padding: 6px; text-align: right;">Precio</th>
            <th style="padding: 6px; text-align: right;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($detalles as $detalle)
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 6px;">{{ $detalle->nombre }}</td>
                <td style="padding: 6px; text-align: right;">{{ $detalle->cantidad }}</td>
                <td style="padding: 6px; text-align: right;">{{ $moneda }} {{ number_format($detalle->precio_unitario, 2) }}</td>
                <td style="padding: 6px; text-align: right;">{{ $moneda }} {{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
