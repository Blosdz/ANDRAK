<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['producto_id', 'cantidad_disponible', 'stock_minimo'])]
class Almacen extends Model
{
    protected $table = 'almacen';

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function scopeStockBajo(Builder $query): Builder
    {
        return $query->whereColumn('cantidad_disponible', '<=', 'stock_minimo');
    }
}
