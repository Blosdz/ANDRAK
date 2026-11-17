{{-- Branded footer bar: contact info on navy background --}}
<table style="width: 100%; background-color: #0b1f3a; margin-top: 10px;">
    <tr>
        <td style="padding: 10px 14px; color: #ffffff; font-size: 9px; width: 68%;">
            @if ($empresa->whatsapp || $empresa->telefono)
                Tel/WhatsApp: {{ $empresa->whatsapp ?: $empresa->telefono }} &nbsp; &nbsp;
            @endif
            @if ($empresa->correo)
                {{ $empresa->correo }} &nbsp; &nbsp;
            @endif
            @if ($empresa->direccion)
                {{ $empresa->direccion }}
            @endif
        </td>
        <td style="padding: 10px 14px; color: #f5a623; font-size: 9px; text-align: right; font-style: italic; width: 32%;">
            {{ $empresa->eslogan }}
        </td>
    </tr>
</table>
