<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Empresa;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $ventas = Venta::query()
            ->with(['cliente', 'cotizacion', 'pagos'])
            ->when($request->buscar, fn ($q, $buscar) => $q->where(function ($q) use ($buscar) {
                $q->where('numero', 'ilike', "%{$buscar}%")
                    ->orWhereHas('cliente', fn ($q) => $q->where('nombre', 'ilike', "%{$buscar}%"));
            }))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($ventas);
    }

    public function show(Venta $venta)
    {
        return response()->json($venta->load([
            'cliente', 'cotizacion.lineas.producto', 'tipoPago', 'comprobantes.tipoComprobante', 'pagos',
        ]));
    }

    public function update(UpdateVentaRequest $request, Venta $venta)
    {
        $venta->update($request->validated());

        return response()->json($venta->load(['cliente', 'cotizacion']));
    }

    public function pdf(Request $request, Venta $venta)
    {
        $venta->load(['cliente', 'cotizacion.lineas.producto', 'tipoPago', 'pagos']);
        $empresa = Empresa::actual()->load('cuentasBancarias');

        // "Parcial" (tipo=recibo): contrato de compra + recibo de pago, sin la
        // seccion de cotizacion. El documento completo incluye las 3 secciones
        // (cotizacion, contrato de compra y recibo de pago).
        $soloRecibo = $request->query('tipo') === 'recibo';

        $equivalentePen = ($venta->moneda === 'USD' && $venta->tipo_cambio && $venta->monto_final_pen)
            ? ['tipo_cambio' => (float) $venta->tipo_cambio, 'total' => (float) $venta->monto_final_pen]
            : null;

        return Pdf::loadView('pdf.contrato', [
            'venta' => $venta,
            'empresa' => $empresa,
            'incluirCotizacion' => ! $soloRecibo,
            'incluirContrato' => true,
            'equivalentePen' => $equivalentePen,
        ])
            ->setPaper('a4')
            ->download(($soloRecibo ? "Recibo-{$venta->numero}.pdf" : "Contrato-{$venta->numero}.pdf"));
    }
}
