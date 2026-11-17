{{-- Branded header bar: logo/name on navy, document title on white, N°/fecha box below --}}
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="width: 58%; background-color: #0b1f3a; padding: 14px 18px; border-right: 6px solid #f5a623;">
            <table style="width: 100%;">
                <tr>
                    @if ($empresa->logo)
                        <td style="width: 60px; vertical-align: middle;">
                            <img src="{{ $empresa->logo }}" style="max-width: 55px; max-height: 55px;">
                        </td>
                    @endif
                    <td style="vertical-align: middle; padding-left: 10px;">
                        <span style="color: #ffffff; font-size: 18px; font-weight: bold;">{{ $empresa->nombre_comercial }}</span><br>
                        @if ($empresa->eslogan)
                            <span style="color: #c9d3e0; font-size: 9px;">{{ $empresa->eslogan }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
        <td style="width: 42%; background-color: #ffffff; text-align: right; padding: 14px 18px; vertical-align: middle;">
            <span style="font-size: 22px; font-weight: bold; color: #0b1f3a; border-bottom: 3px solid #f5a623; padding-bottom: 4px;">{{ $titulo }}</span>
        </td>
    </tr>
</table>

<table style="width: 100%; margin-top: 14px;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="width: 40%; text-align: right;">
            <table style="width: 100%;">
                <tr>
                    <td style="border: 1px solid #f5a623; padding: 6px 10px; text-align: center; font-weight: bold; color: #0b1f3a; font-size: 13px;">
                        N&deg; {{ $numero }}
                    </td>
                </tr>
            </table>
            <div style="margin-top: 6px; font-size: 10px; color: #131b2e;"><strong>FECHA:</strong> {{ $fecha }}</div>
        </td>
    </tr>
</table>
