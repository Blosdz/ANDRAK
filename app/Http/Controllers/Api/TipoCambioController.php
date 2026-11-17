<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TipoCambioService;

class TipoCambioController extends Controller
{
    public function show(TipoCambioService $service)
    {
        $tipoCambio = $service->actual();

        return response()->json([
            'fecha' => $tipoCambio->fecha->toDateString(),
            'compra' => (float) $tipoCambio->compra,
            'venta' => (float) $tipoCambio->venta,
        ]);
    }
}
