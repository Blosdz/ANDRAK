<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'empresa_id', 'venta_id', 'cliente_id', 'tipo_comprobante_id', 'serie', 'numero', 'moneda_id', 'tipo_cambio',
    'fecha_emision', 'hora_emision', 'subtotal', 'total_igv', 'total', 'total_pen', 'estado',
    'hash', 'qr', 'xml_path', 'cdr_path', 'respuesta_sunat', 'has_cdr',
])]
class Comprobante extends Model
{
    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'subtotal' => 'decimal:2',
            'total_igv' => 'decimal:2',
            'total' => 'decimal:2',
            'tipo_cambio' => 'decimal:3',
            'total_pen' => 'decimal:2',
            'respuesta_sunat' => 'array',
            'has_cdr' => 'boolean',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tipoComprobante(): BelongsTo
    {
        return $this->belongsTo(TipoComprobante::class);
    }

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(TipoMoneda::class, 'moneda_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ComprobanteItem::class);
    }

    public function getNumeroCompletoAttribute(): string
    {
        return sprintf('%s-%08d', $this->serie, $this->numero);
    }
}
