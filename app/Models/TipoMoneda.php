<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMoneda extends Model
{
    protected $table = 'tipos_moneda';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
