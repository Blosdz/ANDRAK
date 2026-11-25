<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'logo', 'marca_agua', 'nombre_comercial', 'eslogan', 'razon_social', 'ruc', 'tipo_documento_id', 'direccion',
    'ubigeo', 'establecimiento_codigo', 'telefono', 'whatsapp', 'correo',
    'representante_nombre', 'representante_dni', 'titular_cuenta', 'modo_sunat', 'sunat_usuario_sol',
    'sunat_clave_sol', 'certificado', 'certificado_vencimiento',
])]
class Empresa extends Model
{
    protected $table = 'empresa';

    protected function casts(): array
    {
        return [
            'sunat_usuario_sol' => 'encrypted',
            'sunat_clave_sol' => 'encrypted',
            'certificado_vencimiento' => 'date',
        ];
    }

    public static function actual(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'nombre_comercial' => 'Mi Empresa',
            'ruc' => '',
        ]);
    }

    public function cuentasBancarias(): HasMany
    {
        return $this->hasMany(CuentaBancaria::class)->orderBy('orden');
    }

    public function series(): HasMany
    {
        return $this->hasMany(Serie::class);
    }

    public function tieneSunatConfigurado(): bool
    {
        return (bool) $this->certificado
            && (bool) $this->razon_social
            && (bool) $this->sunat_usuario_sol
            && (bool) $this->sunat_clave_sol
            && strlen($this->ruc) === 11;
    }
}
