<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistroProduccionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_publico_se_bloquea_cuando_esta_desactivado(): void
    {
        config()->set('auth.public_registration', false);

        $this->postJson('/api/register', [
            'nombre' => 'Prueba',
            'mail' => 'prueba@example.com',
            'password' => 'contrasena-segura',
        ])->assertForbidden();

        $this->assertSame(0, Usuario::count());
    }
}
