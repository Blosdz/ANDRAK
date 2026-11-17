<?php

namespace App\Exceptions;

use Exception;

class CotizacionYaConvertidaException extends Exception
{
    protected $message = 'Esta cotizacion ya fue convertida a venta.';
}
