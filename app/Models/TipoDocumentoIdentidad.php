<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumentoIdentidad extends Model
{
    protected $table = 'tipos_documento_identidad';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
