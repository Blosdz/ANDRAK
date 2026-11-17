<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contexto SSL del cliente SOAP
    |--------------------------------------------------------------------------
    |
    | Deshabilita la verificación del certificado TLS del endpoint de SUNAT.
    | Solo debe activarse si el entorno tiene problemas para validar la
    | cadena de certificados de SUNAT (por ejemplo, en desarrollo local).
    |
    */
    'soap_stream_context_ssl' => env('SUNAT_SOAP_STREAM_CONTEXT_SSL', false),

];
