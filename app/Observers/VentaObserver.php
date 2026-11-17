<?php

namespace App\Observers;

use App\Models\Venta;

class VentaObserver
{
    /**
     * Handle the Venta "created" event.
     */
    public function created(Venta $venta): void
    {
        $venta->numero = 'VEN-'.str_pad($venta->id, 6, '0', STR_PAD_LEFT);
        $venta->saveQuietly();
    }
}
