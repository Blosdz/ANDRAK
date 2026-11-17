<?php

namespace App\Http\Controllers\Api;

use App\CoreFacturalo\Facturalo;
use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Models\Empresa;
use App\Models\Serie;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComprobanteController extends Controller
{
    private const IGV = 0.18;

    public function store(Request $request, Venta $venta)
    {
        $request->validate([
            'tipo_comprobante_id' => ['required', 'in:01,03'],
        ]);

        $venta->load(['cliente', 'cotizacion.lineas']);
        $empresa = Empresa::actual();

        if (! $empresa->tieneSunatConfigurado()) {
            return response()->json([
                'message' => 'Falta completar la configuracion SUNAT de la empresa (RUC de 11 digitos, usuario/clave SOL y certificado digital) antes de emitir comprobantes.',
            ], 422);
        }

        $tipoComprobanteId = $request->input('tipo_comprobante_id');
        $cliente = $venta->cliente;

        if ($tipoComprobanteId === '01' && ($cliente->tipo_documento_id !== '6' || strlen((string) $cliente->ruc) !== 11)) {
            return response()->json([
                'message' => 'Para emitir una factura, el cliente debe tener un RUC de 11 digitos.',
            ], 422);
        }

        $lineas = $venta->cotizacion?->lineas ?? collect();
        if ($lineas->isEmpty()) {
            return response()->json(['message' => 'La venta no tiene lineas para facturar.'], 422);
        }

        $comprobante = DB::transaction(function () use ($tipoComprobanteId, $venta, $cliente, $lineas) {
            [$serie, $numero] = Serie::siguienteNumero($tipoComprobanteId);

            $items = $lineas->map(function ($linea) {
                $subtotal = round((float) $linea->cantidad * (float) $linea->precio_unitario, 2);
                $igv = round($subtotal * self::IGV, 2);

                return [
                    'producto_id' => $linea->producto_id,
                    'descripcion' => $linea->nombre,
                    'unidad_medida_id' => 'NIU',
                    'cantidad' => $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                    'tipo_afectacion_igv_id' => '10',
                    'igv' => $igv,
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $igv,
                ];
            });

            $subtotal = $items->sum('subtotal');
            $totalIgv = $items->sum('igv');
            $total = $subtotal + $totalIgv;

            $tipoCambio = $venta->moneda === 'USD' ? $venta->tipo_cambio : null;
            $totalPen = $tipoCambio ? round($total * (float) $tipoCambio, 2) : null;

            $comprobante = Comprobante::create([
                'venta_id' => $venta->id,
                'cliente_id' => $cliente->id,
                'tipo_comprobante_id' => $tipoComprobanteId,
                'serie' => $serie,
                'numero' => $numero,
                'moneda_id' => in_array($venta->moneda, ['PEN', 'USD']) ? $venta->moneda : 'PEN',
                'tipo_cambio' => $tipoCambio,
                'fecha_emision' => now()->toDateString(),
                'hora_emision' => now()->format('H:i:s'),
                'subtotal' => $subtotal,
                'total_igv' => $totalIgv,
                'total' => $total,
                'total_pen' => $totalPen,
                'estado' => 'pendiente',
            ]);

            $comprobante->items()->createMany($items->all());

            return $comprobante;
        });

        try {
            $comprobante = (new Facturalo())->emitir($comprobante);
        } catch (\Throwable $e) {
            return response()->json([
                'comprobante' => $comprobante->fresh(['items', 'cliente', 'tipoComprobante']),
                'message' => 'El comprobante se registro pero no se pudo enviar a SUNAT: '.$e->getMessage(),
            ], 502);
        }

        return response()->json($comprobante->load(['items', 'cliente', 'tipoComprobante']), 201);
    }

    public function show(Comprobante $comprobante)
    {
        return response()->json($comprobante->load(['items', 'cliente', 'tipoComprobante', 'venta']));
    }

    public function reenviar(Comprobante $comprobante)
    {
        try {
            $comprobante = (new Facturalo())->emitir($comprobante);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No se pudo reenviar el comprobante: '.$e->getMessage(),
            ], 502);
        }

        return response()->json($comprobante->load(['items', 'cliente', 'tipoComprobante']));
    }

    public function pdf(Comprobante $comprobante)
    {
        $comprobante->load(['items', 'cliente', 'tipoComprobante']);
        $empresa = Empresa::actual();

        return Pdf::loadView('pdf.comprobante', ['comprobante' => $comprobante, 'empresa' => $empresa])
            ->setPaper('a4')
            ->download("{$comprobante->serie}-{$comprobante->numero}.pdf");
    }

    public function xml(Comprobante $comprobante)
    {
        if (! $comprobante->xml_path || ! Storage::exists($comprobante->xml_path)) {
            return response()->json(['message' => 'El comprobante aun no tiene un XML generado.'], 404);
        }

        return Storage::download($comprobante->xml_path, "{$comprobante->serie}-{$comprobante->numero}.xml");
    }

    public function cdr(Comprobante $comprobante)
    {
        if (! $comprobante->cdr_path || ! Storage::exists($comprobante->cdr_path)) {
            return response()->json(['message' => 'El comprobante aun no tiene un CDR de SUNAT.'], 404);
        }

        return Storage::download($comprobante->cdr_path, "{$comprobante->serie}-{$comprobante->numero}-cdr.zip");
    }
}
