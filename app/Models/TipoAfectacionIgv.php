<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAfectacionIgv extends Model
{
    protected $table = 'tipos_afectacion_igv';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'porcentaje' => 'decimal:2',
        ];
    }
}
