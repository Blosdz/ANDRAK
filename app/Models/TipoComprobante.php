<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoComprobante extends Model
{
    protected $table = 'tipos_comprobante';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
