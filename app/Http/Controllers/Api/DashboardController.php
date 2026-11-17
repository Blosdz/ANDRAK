<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Venta;

class DashboardController extends Controller
{
    public function index()
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        return response()->json([
            'total_productos' => Producto::count(),
            'stock_total' => (int) Almacen::sum('cantidad_disponible'),
            'total_clientes' => Cliente::count(),
            'cotizaciones_mes' => Cotizacion::whereBetween('created_at', [$inicioMes, $finMes])->count(),
            'ventas_mes' => Venta::whereBetween('created_at', [$inicioMes, $finMes])->count(),
            'monto_vendido_pen' => (float) Venta::whereBetween('created_at', [$inicioMes, $finMes])->where('moneda', 'PEN')->sum('monto_final'),
            'monto_vendido_usd' => (float) Venta::whereBetween('created_at', [$inicioMes, $finMes])->where('moneda', 'USD')->sum('monto_final'),
            'ultimas_cotizaciones' => Cotizacion::with('cliente')->latest()->limit(5)->get(),
            'stock_bajo' => Almacen::stockBajo()->with('producto')->get(),
        ]);
    }
}
