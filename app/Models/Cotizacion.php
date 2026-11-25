<?php

namespace App\Models;

use App\Observers\CotizacionObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['empresa_id', 'cliente_id', 'tipo_pago_id', 'numero', 'estado', 'moneda', 'subtotal', 'impuestos', 'total', 'horas_entrega', 'detalles', 'referencia', 'validez_dias', 'garantia'])]
#[ObservedBy(CotizacionObserver::class)]
class Cotizacion extends Model
{
    use SoftDeletes;

    protected $table = 'cotizaciones';

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(CotizacionDetalle::class);
    }

    public function venta(): HasOne
    {
        return $this->hasOne(Venta::class);
    }
}
