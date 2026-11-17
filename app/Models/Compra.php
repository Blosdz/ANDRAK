<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['producto_id', 'cantidad', 'costo_unitario', 'proveedor', 'fecha_compra'])]
class Compra extends Model
{
    protected function casts(): array
    {
        return [
            'costo_unitario' => 'decimal:2',
            'fecha_compra' => 'date',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
