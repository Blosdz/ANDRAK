<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCambio extends Model
{
    protected $table = 'tipos_cambio';

    protected $fillable = ['fecha', 'compra', 'venta'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'compra' => 'decimal:3',
            'venta' => 'decimal:3',
        ];
    }
}
