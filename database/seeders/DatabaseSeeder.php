<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $empresa = Empresa::updateOrCreate(['id' => 1], [
            'nombre_comercial' => 'ANDRAK',
            'razon_social' => 'CORPORACIONES ANDRAK S.A.C.',
            'ruc' => '20616592395',
            'direccion' => 'Cerro colorado - Arequipa - Perú',
            'titular_cuenta' => 'CORPORACIONES ANDRAK S.A.C.',
            'representante_nombre' => 'LIC. ANDREA MARIET LIMA ESCOBEDO',
        ]);

        $empresa->cuentasBancarias()->updateOrCreate(
            ['banco' => 'Interbank', 'moneda' => 'PEN'],
            [
                'numero_cuenta' => '300-3008735528',
                'cci' => '003-300-003008735528-12',
                'orden' => 0,
            ]
        );

        $this->call(SunatCatalogSeeder::class);
    }
}
