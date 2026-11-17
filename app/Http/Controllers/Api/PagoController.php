<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePagoRequest;
use App\Models\Pago;
use App\Models\Venta;

class PagoController extends Controller
{
    public function store(StorePagoRequest $request, Venta $venta)
    {
        $venta->load('pagos');
        $monto = $request->float('monto');

        if ($monto > $venta->saldo_pendiente) {
            return response()->json([
                'message' => 'El monto excede el saldo pendiente de la venta.',
            ], 422);
        }

        $pago = $venta->pagos()->create([
            'monto' => $monto,
            'fecha_pago' => $request->date('fecha_pago'),
            'nota' => $request->input('nota'),
        ]);

        return response()->json($pago, 201);
    }

    public function destroy(Venta $venta, Pago $pago)
    {
        abort_if($pago->venta_id !== $venta->id, 404);

        $pago->delete();

        return response()->json(null, 204);
    }
}
