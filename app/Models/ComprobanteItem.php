<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'comprobante_id', 'producto_id', 'descripcion', 'unidad_medida_id', 'cantidad',
    'precio_unitario', 'tipo_afectacion_igv_id', 'igv', 'subtotal', 'total',
])]
class ComprobanteItem extends Model
{
    protected $table = 'comprobante_items';

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'igv' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    public function tipoAfectacionIgv(): BelongsTo
    {
        return $this->belongsTo(TipoAfectacionIgv::class, 'tipo_afectacion_igv_id');
    }

    public function getNombreAttribute(): string
    {
        return $this->descripcion;
    }
}
