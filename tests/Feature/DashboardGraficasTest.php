<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardGraficasTest extends TestCase
{
    use RefreshDatabase;

    public function test_graficas_y_totales_respetan_el_emisor_y_la_moneda(): void
    {
        $this->seed();
        Sanctum::actingAs(Usuario::factory()->create());

        $segundo = Empresa::create([
            'nombre_comercial' => 'Segundo emisor',
            'razon_social' => 'Segundo emisor',
            'ruc' => '10730862453',
        ]);
        $cliente = Cliente::create(['nombre' => 'Cliente prueba']);
        $mes = now()->month - 1;

        $cotizacionUno = Cotizacion::create(['empresa_id' => 1, 'cliente_id' => $cliente->id, 'moneda' => 'PEN', 'total' => 100]);
        Venta::create(['cotizacion_id' => $cotizacionUno->id, 'cliente_id' => $cliente->id, 'moneda' => 'PEN', 'monto_final' => 100]);

        $cotizacionDos = Cotizacion::create(['empresa_id' => $segundo->id, 'cliente_id' => $cliente->id, 'moneda' => 'USD', 'total' => 50]);
        Venta::create(['cotizacion_id' => $cotizacionDos->id, 'cliente_id' => $cliente->id, 'moneda' => 'USD', 'monto_final' => 50]);
        $this->travelTo(now()->subYear());
        $cotizacionAnterior = Cotizacion::create(['empresa_id' => $segundo->id, 'cliente_id' => $cliente->id, 'moneda' => 'USD', 'total' => 25]);
        Venta::create(['cotizacion_id' => $cotizacionAnterior->id, 'cliente_id' => $cliente->id, 'moneda' => 'USD', 'monto_final' => 25]);
        $this->travelBack();
        Comprobante::create([
            'empresa_id' => $segundo->id,
            'cliente_id' => $cliente->id,
            'tipo_comprobante_id' => '01',
            'serie' => 'F001',
            'numero' => 1,
            'fecha_emision' => now()->toDateString(),
            'hora_emision' => now()->format('H:i:s'),
        ]);

        $this->getJson('/api/dashboard?empresa_id='.$segundo->id.'&anio='.now()->year)
            ->assertOk()
            ->assertJsonPath('total_clientes', 1)
            ->assertJsonPath('ventas_mes', 1)
            ->assertJsonPath('monto_vendido_pen', 0)
            ->assertJsonPath('monto_vendido_usd', 50)
            ->assertJsonPath('facturas_mes', 1)
            ->assertJsonPath("grafica.cotizaciones.{$mes}", 1)
            ->assertJsonPath("grafica.ventas.{$mes}", 1)
            ->assertJsonPath("grafica.facturas.{$mes}", 1)
            ->assertJsonPath("grafica.ventas_pen.{$mes}", 0)
            ->assertJsonPath("grafica.ventas_usd.{$mes}", 50)
            ->assertJsonPath("grafica.ventas_usd_anterior.{$mes}", 25);

        $this->getJson('/api/dashboard?anio='.now()->year)
            ->assertOk()
            ->assertJsonPath('ventas_mes', 2)
            ->assertJsonPath("grafica.ventas.{$mes}", 2)
            ->assertJsonPath("grafica.ventas_pen.{$mes}", 100);

        $this->getJson('/api/dashboard?empresa_id=999999')->assertUnprocessable();
    }
}
