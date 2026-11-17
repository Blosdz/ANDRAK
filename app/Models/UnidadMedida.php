<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidades_medida';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
