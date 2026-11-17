<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificadoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ComprobanteController;
use App\Http\Controllers\Api\CotizacionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\SerieController;
use App\Http\Controllers\Api\TipoCambioController;
use App\Http\Controllers\Api\VentaController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/empresa', [EmpresaController::class, 'show']);
    Route::put('/empresa', [EmpresaController::class, 'update']);

    Route::get('/certificado', [CertificadoController::class, 'show']);
    Route::post('/certificado', [CertificadoController::class, 'store']);
    Route::delete('/certificado', [CertificadoController::class, 'destroy']);

    Route::get('/productos/plantilla-excel', [ProductoController::class, 'plantillaExcel']);
    Route::post('/productos/importar-excel', [ProductoController::class, 'importarExcel']);
    Route::apiResource('productos', ProductoController::class);
    Route::apiResource('clientes', ClienteController::class);

    Route::apiResource('cotizaciones', CotizacionController::class)
        ->parameters(['cotizaciones' => 'cotizacion']);
    Route::post('/cotizaciones/{cotizacion}/convertir-venta', [CotizacionController::class, 'convertirVenta']);
    Route::get('/cotizaciones/{cotizacion}/pdf', [CotizacionController::class, 'pdf']);

    Route::apiResource('ventas', VentaController::class)->only(['index', 'show', 'update']);
    Route::get('/ventas/{venta}/pdf', [VentaController::class, 'pdf']);
    Route::post('/ventas/{venta}/pagos', [PagoController::class, 'store']);
    Route::delete('/ventas/{venta}/pagos/{pago}', [PagoController::class, 'destroy']);
    Route::post('/ventas/{venta}/comprobantes', [ComprobanteController::class, 'store']);

    Route::get('/comprobantes/{comprobante}', [ComprobanteController::class, 'show']);
    Route::post('/comprobantes/{comprobante}/reenviar', [ComprobanteController::class, 'reenviar']);
    Route::get('/comprobantes/{comprobante}/pdf', [ComprobanteController::class, 'pdf']);
    Route::get('/comprobantes/{comprobante}/xml', [ComprobanteController::class, 'xml']);
    Route::get('/comprobantes/{comprobante}/cdr', [ComprobanteController::class, 'cdr']);

    Route::get('/series', [SerieController::class, 'index']);
    Route::put('/series/{serie}', [SerieController::class, 'update']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/tipo-cambio', [TipoCambioController::class, 'show']);
});
