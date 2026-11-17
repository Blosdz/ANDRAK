<?php

namespace App\Models;

use App\Observers\VentaObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['cotizacion_id', 'cliente_id', 'tipo_pago_id', 'numero', 'monto_final', 'moneda', 'tipo_cambio', 'monto_final_pen', 'cuotas', 'url_pago'])]
#[ObservedBy(VentaObserver::class)]
class Venta extends Model
{
    protected $appends = ['monto_pagado', 'saldo_pendiente', 'porcentaje_pagado'];

    protected function casts(): array
    {
        return [
            'monto_final' => 'decimal:2',
            'tipo_cambio' => 'decimal:3',
            'monto_final_pen' => 'decimal:2',
        ];
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    public function comprobantes(): HasMany
    {
        return $this->hasMany(Comprobante::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class)->orderBy('fecha_pago');
    }

    protected function montoPagado(): Attribute
    {
        return Attribute::get(fn () => round((float) $this->pagos->sum('monto'), 2));
    }

    protected function saldoPendiente(): Attribute
    {
        return Attribute::get(fn () => round((float) $this->monto_final - $this->monto_pagado, 2));
    }

    protected function porcentajePagado(): Attribute
    {
        return Attribute::get(fn () => (float) $this->monto_final > 0
            ? round(($this->monto_pagado / (float) $this->monto_final) * 100)
            : 0);
    }
}
