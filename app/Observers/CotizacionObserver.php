<?php

namespace App\Observers;

use App\Models\Cotizacion;

class CotizacionObserver
{
    /**
     * Handle the Cotizacion "created" event.
     */
    public function created(Cotizacion $cotizacion): void
    {
        $cotizacion->numero = 'COT-'.str_pad($cotizacion->id, 6, '0', STR_PAD_LEFT);
        $cotizacion->saveQuietly();
    }
}
