<table class="mt-16">
    <tr>
        <td width="70%"></td>
        <td width="30%">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">{{ $moneda }} {{ number_format($subtotal, 2) }}</td>
                </tr>
                @if (! is_null($impuestos))
                    <tr>
                        <td>Impuestos</td>
                        <td class="text-right">{{ $moneda }} {{ number_format($impuestos, 2) }}</td>
                    </tr>
                @endif
                <tr style="border-top: 1px solid #d8d8e8;">
                    <td><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ $moneda }} {{ number_format($total, 2) }}</strong></td>
                </tr>
                @if (! empty($totalPen))
                    <tr>
                        <td>Equivalente{{ ! empty($tipoCambio) ? ' (S/ '.number_format($tipoCambio, 3).')' : '' }}</td>
                        <td class="text-right">S/ {{ number_format($totalPen, 2) }}</td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>
