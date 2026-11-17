<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[Fillable(['tipo_comprobante_id', 'serie', 'correlativo'])]
class Serie extends Model
{
    public function tipoComprobante(): BelongsTo
    {
        return $this->belongsTo(TipoComprobante::class);
    }

    /**
     * Reserva y retorna el siguiente número de correlativo para el tipo de comprobante dado.
     */
    public static function siguienteNumero(string $tipoComprobanteId): array
    {
        return DB::transaction(function () use ($tipoComprobanteId) {
            $serie = static::where('tipo_comprobante_id', $tipoComprobanteId)->lockForUpdate()->firstOrFail();
            $serie->correlativo += 1;
            $serie->save();

            return [$serie->serie, $serie->correlativo];
        });
    }
}
