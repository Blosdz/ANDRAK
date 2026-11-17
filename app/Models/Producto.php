<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nombre', 'codigo', 'descripcion', 'categoria', 'precio', 'moneda', 'descripcion_extra', 'img', 'estado'])]
class Producto extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'descripcion_extra' => 'array',
        ];
    }

    public function cotizacionDetalles(): HasMany
    {
        return $this->hasMany(CotizacionDetalle::class);
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }

    public function almacen(): HasOne
    {
        return $this->hasOne(Almacen::class);
    }
}
