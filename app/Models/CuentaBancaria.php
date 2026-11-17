<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['empresa_id', 'banco', 'moneda', 'numero_cuenta', 'cci', 'orden'])]
class CuentaBancaria extends Model
{
    protected $table = 'cuentas_bancarias';

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
