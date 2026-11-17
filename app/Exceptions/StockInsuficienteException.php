<?php

namespace App\Exceptions;

use Exception;

class StockInsuficienteException extends Exception
{
    protected $message = 'No hay stock suficiente para completar la conversion.';

    /**
     * @param  array<int, array{producto_id: int, nombre: string, solicitado: int, disponible: int}>  $faltantes
     */
    public function __construct(public readonly array $faltantes)
    {
        parent::__construct();
    }
}
