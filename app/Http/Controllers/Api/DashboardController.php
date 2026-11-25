<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    private const MESES = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'empresa_id' => ['nullable', 'integer', Rule::exists('empresa', 'id')],
            'anio' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $empresaId = $filtros['empresa_id'] ?? null;
        $anio = $filtros['anio'] ?? now()->year;
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();
        $ventas = fn () => Venta::query()->when($empresaId, fn ($q) => $q->whereHas('cotizacion', fn ($c) => $c->where('empresa_id', $empresaId)));
        $cotizaciones = fn () => Cotizacion::query()->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId));
        $comprobantes = fn () => Comprobante::query()->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId));

        $serieVentas = $this->ventasPorMes($ventas(), $anio);
        $serieAnterior = $this->ventasPorMes($ventas(), $anio - 1);
        $serieCotizaciones = $this->conteosPorMes($cotizaciones(), $anio);
        $serieComprobantes = $comprobantes()
            ->whereBetween('fecha_emision', ["{$anio}-01-01", "{$anio}-12-31"])
            ->selectRaw("substr(fecha_emision, 1, 7) as mes, SUM(CASE WHEN tipo_comprobante_id = '01' THEN 1 ELSE 0 END) as facturas, SUM(CASE WHEN tipo_comprobante_id = '03' THEN 1 ELSE 0 END) as boletas")
            ->groupByRaw('substr(fecha_emision, 1, 7)')
            ->get()->keyBy('mes');

        return response()->json([
            'total_productos' => Producto::count(),
            'stock_total' => (int) Almacen::sum('cantidad_disponible'),
            'total_clientes' => $empresaId
                ? Cliente::whereHas('cotizaciones', fn ($q) => $q->where('empresa_id', $empresaId))->count()
                : Cliente::count(),
            'cotizaciones_mes' => $cotizaciones()->whereBetween('created_at', [$inicioMes, $finMes])->count(),
            'ventas_mes' => $ventas()->whereBetween('created_at', [$inicioMes, $finMes])->count(),
            'monto_vendido_pen' => (float) $ventas()->whereBetween('created_at', [$inicioMes, $finMes])->where('moneda', 'PEN')->sum('monto_final'),
            'monto_vendido_usd' => (float) $ventas()->whereBetween('created_at', [$inicioMes, $finMes])->where('moneda', 'USD')->sum('monto_final'),
            'facturas_mes' => $comprobantes()->where('tipo_comprobante_id', '01')->whereBetween('fecha_emision', [$inicioMes->toDateString(), $finMes->toDateString()])->count(),
            'boletas_mes' => $comprobantes()->where('tipo_comprobante_id', '03')->whereBetween('fecha_emision', [$inicioMes->toDateString(), $finMes->toDateString()])->count(),
            'ultimas_cotizaciones' => $cotizaciones()->with('cliente')->latest()->limit(5)->get(),
            'stock_bajo' => Almacen::stockBajo()->with('producto')->get(),
            'emisores' => Empresa::query()->select('id', 'nombre_comercial', 'razon_social', 'ruc')->orderBy('id')->get(),
            'grafica' => [
                'anio' => $anio,
                'meses' => self::MESES,
                'cotizaciones' => $this->valoresMes($serieCotizaciones, $anio, 'cantidad'),
                'ventas' => $this->valoresMes($serieVentas, $anio, 'cantidad'),
                'facturas' => $this->valoresMes($serieComprobantes, $anio, 'facturas'),
                'boletas' => $this->valoresMes($serieComprobantes, $anio, 'boletas'),
                'ventas_pen' => $this->valoresMes($serieVentas, $anio, 'pen'),
                'ventas_usd' => $this->valoresMes($serieVentas, $anio, 'usd'),
                'ventas_pen_anterior' => $this->valoresMes($serieAnterior, $anio - 1, 'pen'),
                'ventas_usd_anterior' => $this->valoresMes($serieAnterior, $anio - 1, 'usd'),
            ],
        ]);
    }

    private function ventasPorMes($query, int $anio)
    {
        return $query->whereBetween('created_at', ["{$anio}-01-01 00:00:00", "{$anio}-12-31 23:59:59"])
            ->selectRaw("substr(created_at, 1, 7) as mes, COUNT(*) as cantidad, SUM(CASE WHEN moneda = 'PEN' THEN monto_final ELSE 0 END) as pen, SUM(CASE WHEN moneda = 'USD' THEN monto_final ELSE 0 END) as usd")
            ->groupByRaw('substr(created_at, 1, 7)')
            ->get()->keyBy('mes');
    }

    private function conteosPorMes($query, int $anio)
    {
        return $query->whereBetween('created_at', ["{$anio}-01-01 00:00:00", "{$anio}-12-31 23:59:59"])
            ->selectRaw('substr(created_at, 1, 7) as mes, COUNT(*) as cantidad')
            ->groupByRaw('substr(created_at, 1, 7)')
            ->get()->keyBy('mes');
    }

    private function valoresMes($registros, int $anio, string $campo): array
    {
        return array_map(
            fn ($mes) => (float) ($registros->get(sprintf('%d-%02d', $anio, $mes))?->{$campo} ?? 0),
            range(1, 12)
        );
    }
}
