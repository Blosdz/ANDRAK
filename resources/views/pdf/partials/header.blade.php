<table>
    <tr>
        <td width="70">
            @if ($empresa->logo)
                <img src="{{ $empresa->logo }}" style="max-width: 70px; max-height: 70px;">
            @endif
        </td>
        <td>
            <strong style="font-size: 16px;">{{ $empresa->nombre_comercial }}</strong><br>
            @if ($empresa->ruc) RUC: {{ $empresa->ruc }}<br> @endif
            @if ($empresa->direccion) {{ $empresa->direccion }}<br> @endif
            @if ($empresa->telefono) {{ $empresa->telefono }} @endif
        </td>
    </tr>
</table>
<hr style="border: none; border-top: 1px solid #d8d8e8; margin-top: 12px;">
