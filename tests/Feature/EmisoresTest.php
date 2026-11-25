<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Serie;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmisoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_cotizacion_y_series_conservan_el_emisor_elegido(): void
    {
        $this->seed();
        Sanctum::actingAs(Usuario::factory()->create());

        $segundo = $this->postJson('/api/empresas', [
            'nombre_comercial' => 'Emisor de prueba',
            'razon_social' => 'Titular de prueba',
            'ruc' => '10730862453',
        ])->assertCreated()->json();

        $this->postJson('/api/series', [
            'empresa_id' => $segundo['id'],
            'tipo_comprobante_id' => '01',
            'serie' => 'F001',
            'correlativo' => 0,
        ])->assertCreated();

        $this->assertSame(['F001', 1], Serie::siguienteNumero('01', 1));
        $this->assertSame(['F001', 1], Serie::siguienteNumero('01', $segundo['id']));

        $cliente = $this->postJson('/api/clientes', ['nombre' => 'Cliente de prueba'])
            ->assertCreated()->json();
        $producto = Producto::create(['nombre' => 'Producto de prueba', 'precio' => 100, 'moneda' => 'PEN']);

        $this->postJson('/api/cotizaciones', [
            'empresa_id' => $segundo['id'],
            'cliente_id' => $cliente['id'],
            'moneda' => 'PEN',
            'lineas' => [['producto_id' => $producto->id, 'cantidad' => 1, 'precio_unitario' => 100]],
        ])->assertCreated()->assertJsonPath('empresa.ruc', '10730862453');

        $this->assertSame('20616592395', Empresa::findOrFail(1)->ruc);
    }
}
