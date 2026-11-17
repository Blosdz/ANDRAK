<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nombre', 'tipo_documento_id', 'ruc', 'correo', 'empresa', 'direccion', 'telefono', 'contacto'])]
class Cliente extends Model
{
    use SoftDeletes;

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function comprobantes(): HasMany
    {
        return $this->hasMany(Comprobante::class);
    }
}
