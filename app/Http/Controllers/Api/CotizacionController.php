<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CotizacionYaConvertidaException;
use App\Exceptions\StockInsuficienteException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConvertirVentaRequest;
use App\Http\Requests\StoreCotizacionRequest;
use App\Http\Requests\UpdateCotizacionRequest;
use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\Producto;
use App\Services\CotizacionAVentaService;
use App\Services\TipoCambioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $cotizaciones = Cotizacion::query()
            ->with(['cliente', 'lineas', 'empresa'])
            ->when($request->buscar, fn ($q, $buscar) => $q->where(function ($q) use ($buscar) {
                $q->where('numero', 'ilike', "%{$buscar}%")
                    ->orWhereHas('cliente', fn ($q) => $q->where('nombre', 'ilike', "%{$buscar}%"));
            }))
            ->when($request->estado, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($request->cliente_id, fn ($q, $clienteId) => $q->where('cliente_id', $clienteId))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($cotizaciones);
    }

    public function show(Cotizacion $cotizacion, TipoCambioService $tipoCambioService)
    {
        $cotizacion->load(['cliente', 'lineas.producto', 'tipoPago', 'venta', 'empresa']);

        return response()->json([
            ...$cotizacion->toArray(),
            'equivalente_pen' => $this->equivalentePen($cotizacion, $tipoCambioService),
        ]);
    }

    /**
     * Monto equivalente en soles calculado con el tipo de cambio del dia (SUNAT).
     * Solo aplica a cotizaciones en USD; el valor no se guarda, se recalcula
     * "en vivo" cada vez que se consulta la cotizacion.
     */
    private function equivalentePen(Cotizacion $cotizacion, TipoCambioService $tipoCambioService): ?array
    {
        if ($cotizacion->moneda !== 'USD') {
            return null;
        }

        try {
            $tipoCambio = $tipoCambioService->actual();
        } catch (\RuntimeException) {
            return null;
        }

        $venta = (float) $tipoCambio->venta;

        return [
            'tipo_cambio' => $venta,
            'fecha' => $tipoCambio->fecha->toDateString(),
            'total' => round((float) $cotizacion->total * $venta, 2),
        ];
    }

    public function store(StoreCotizacionRequest $request)
    {
        $cotizacion = DB::transaction(function () use ($request) {
            $lineas = collect($request->validated('lineas'))->map(function ($linea) {
                $producto = Producto::findOrFail($linea['producto_id']);

                return [
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'subtotal' => round($linea['cantidad'] * $linea['precio_unitario'], 2),
                ];
            });

            $subtotal = $lineas->sum('subtotal');
            $impuestos = $request->input('impuestos');

            $cotizacion = Cotizacion::create([
                'empresa_id' => $request->integer('empresa_id'),
                'cliente_id' => $request->cliente_id,
                'tipo_pago_id' => $request->tipo_pago_id,
                'moneda' => $request->moneda,
                'horas_entrega' => $request->horas_entrega,
                'detalles' => $request->detalles,
                'referencia' => $request->referencia,
                'validez_dias' => $request->validez_dias ?? 5,
                'garantia' => $request->garantia,
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $subtotal + ($impuestos ?? 0),
            ]);

            $cotizacion->lineas()->createMany($lineas->all());

            return $cotizacion;
        });

        return response()->json($cotizacion->load(['cliente', 'lineas.producto', 'empresa']), 201);
    }

    public function update(UpdateCotizacionRequest $request, Cotizacion $cotizacion)
    {
        if ($cotizacion->estado === 'convertida') {
            return response()->json(['message' => 'No se puede editar una cotizacion ya convertida.'], 409);
        }

        DB::transaction(function () use ($request, $cotizacion) {
            $data = $request->safe()->except(['lineas', 'impuestos']);

            if ($request->has('lineas')) {
                $lineas = collect($request->validated('lineas'))->map(function ($linea) {
                    $producto = Producto::findOrFail($linea['producto_id']);

                    return [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'cantidad' => $linea['cantidad'],
                        'precio_unitario' => $linea['precio_unitario'],
                        'subtotal' => round($linea['cantidad'] * $linea['precio_unitario'], 2),
                    ];
                });

                $cotizacion->lineas()->delete();
                $cotizacion->lineas()->createMany($lineas->all());

                $subtotal = $lineas->sum('subtotal');
                $impuestos = $request->input('impuestos', $cotizacion->impuestos);
                $data['subtotal'] = $subtotal;
                $data['impuestos'] = $impuestos;
                $data['total'] = $subtotal + ($impuestos ?? 0);
            } elseif ($request->has('impuestos')) {
                $impuestos = $request->input('impuestos');
                $data['impuestos'] = $impuestos;
                $data['total'] = $cotizacion->subtotal + ($impuestos ?? 0);
            }

            $cotizacion->update($data);
        });

        return response()->json($cotizacion->load(['cliente', 'lineas.producto', 'empresa']));
    }

    public function destroy(Cotizacion $cotizacion)
    {
        if ($cotizacion->estado === 'convertida') {
            return response()->json(['message' => 'No se puede eliminar una cotizacion ya convertida.'], 409);
        }

        $cotizacion->delete();

        return response()->json(null, 204);
    }

    public function convertirVenta(ConvertirVentaRequest $request, Cotizacion $cotizacion, CotizacionAVentaService $service)
    {
        try {
            $venta = $service->convertir($cotizacion, $request->validated());
        } catch (CotizacionYaConvertidaException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (StockInsuficienteException $e) {
            return response()->json(['message' => $e->getMessage(), 'faltantes' => $e->faltantes], 422);
        }

        return response()->json($venta->load(['cliente', 'cotizacion']), 201);
    }

    public function pdf(Cotizacion $cotizacion, TipoCambioService $tipoCambioService)
    {
        $cotizacion->load(['cliente', 'lineas.producto', 'tipoPago']);
        $empresa = $cotizacion->empresa ?? Empresa::actual();
        $equivalentePen = $this->equivalentePen($cotizacion, $tipoCambioService);

        return Pdf::loadView('pdf.cotizacion', ['cotizacion' => $cotizacion, 'empresa' => $empresa, 'equivalentePen' => $equivalentePen])
            ->setPaper('a4')
            ->download("{$cotizacion->numero}.pdf");
    }
}
