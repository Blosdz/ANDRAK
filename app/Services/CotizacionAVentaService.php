<?php

namespace App\Services;

use App\Exceptions\CotizacionYaConvertidaException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Almacen;
use App\Models\Cotizacion;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class CotizacionAVentaService
{
    public function __construct(private readonly TipoCambioService $tipoCambioService)
    {
    }

    /**
     * @param  array{tipo_pago_id?: int|null, cuotas?: int|null, url_pago?: string|null}  $overrides
     */
    public function convertir(Cotizacion $cotizacion, array $overrides = []): Venta
    {
        return DB::transaction(function () use ($cotizacion, $overrides) {
            $cotizacion = Cotizacion::with('lineas')->lockForUpdate()->findOrFail($cotizacion->id);

            if ($cotizacion->venta()->exists()) {
                throw new CotizacionYaConvertidaException();
            }

            $faltantes = [];

            foreach ($cotizacion->lineas as $detalle) {
                $almacen = Almacen::where('producto_id', $detalle->producto_id)->lockForUpdate()->first();
                $disponible = $almacen?->cantidad_disponible ?? 0;

                if ($disponible < $detalle->cantidad) {
                    $faltantes[] = [
                        'producto_id' => $detalle->producto_id,
                        'nombre' => $detalle->nombre,
                        'solicitado' => $detalle->cantidad,
                        'disponible' => $disponible,
                    ];
                }
            }

            if ($faltantes) {
                throw new StockInsuficienteException($faltantes);
            }

            foreach ($cotizacion->lineas as $detalle) {
                Almacen::where('producto_id', $detalle->producto_id)
                    ->decrement('cantidad_disponible', $detalle->cantidad);
            }

            $tipoCambio = null;
            $montoFinalPen = null;

            if ($cotizacion->moneda === 'USD') {
                try {
                    $tipoCambio = (float) $this->tipoCambioService->actual()->venta;
                    $montoFinalPen = round((float) $cotizacion->total * $tipoCambio, 2);
                } catch (\RuntimeException) {
                    $tipoCambio = null;
                    $montoFinalPen = null;
                }
            }

            $venta = Venta::create([
                'cotizacion_id' => $cotizacion->id,
                'cliente_id' => $cotizacion->cliente_id,
                'tipo_pago_id' => $overrides['tipo_pago_id'] ?? $cotizacion->tipo_pago_id,
                'monto_final' => $cotizacion->total,
                'moneda' => $cotizacion->moneda,
                'tipo_cambio' => $tipoCambio,
                'monto_final_pen' => $montoFinalPen,
                'cuotas' => $overrides['cuotas'] ?? 1,
                'url_pago' => $overrides['url_pago'] ?? null,
            ]);

            $cotizacion->update(['estado' => 'convertida']);

            return $venta;
        });
    }
}
