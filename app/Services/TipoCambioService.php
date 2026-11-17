<?php

namespace App\Services;

use App\Models\TipoCambio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TipoCambioService
{
    private const URL = 'https://www.sunat.gob.pe/a/txt/tipoCambio.txt';

    /**
     * Tipo de cambio del dia (segun SUNAT). Se cachea por dia para no golpear
     * el servicio de SUNAT en cada request; si el fetch falla, cae al ultimo
     * registro guardado en base de datos.
     *
     * Se cachea un array plano (no el modelo Eloquent) porque cachear objetos
     * Eloquent con el driver de base de datos/archivo es fragil: si cambia el
     * autoload entre el momento en que se guarda y el momento en que se lee
     * (deploy, dump-autoload, proceso de larga duracion), unserialize()
     * devuelve un __PHP_Incomplete_Class en vez del modelo.
     */
    public function actual(): TipoCambio
    {
        $datos = Cache::remember(
            'tipo_cambio:'.now()->toDateString(),
            now()->endOfDay(),
            fn () => $this->obtenerOFallback()
        );

        return new TipoCambio($datos);
    }

    /**
     * @return array{fecha: string, compra: float, venta: float}
     */
    private function obtenerOFallback(): array
    {
        $remoto = $this->fetchDesdeSunat();

        if ($remoto) {
            TipoCambio::updateOrCreate(['fecha' => $remoto['fecha']], [
                'compra' => $remoto['compra'],
                'venta' => $remoto['venta'],
            ]);

            return $remoto;
        }

        $ultimo = TipoCambio::orderByDesc('fecha')->first();

        if (! $ultimo) {
            throw new \RuntimeException('No se pudo obtener el tipo de cambio de SUNAT y no hay ningun valor previo guardado.');
        }

        return [
            'fecha' => $ultimo->fecha->toDateString(),
            'compra' => (float) $ultimo->compra,
            'venta' => (float) $ultimo->venta,
        ];
    }

    /**
     * @return array{fecha: string, compra: float, venta: float}|null
     */
    private function fetchDesdeSunat(): ?array
    {
        try {
            $respuesta = Http::timeout(5)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36')
                ->get(self::URL);

            if (! $respuesta->successful()) {
                return null;
            }

            $lineas = array_values(array_filter(array_map('trim', explode("\n", $respuesta->body()))));
            $ultimaLinea = end($lineas);

            if (! $ultimaLinea) {
                return null;
            }

            $partes = explode('|', trim($ultimaLinea, '|'));

            if (count($partes) !== 3) {
                return null;
            }

            [$fecha, $compra, $venta] = $partes;

            $fecha = \DateTime::createFromFormat('d/m/Y', trim($fecha));

            if (! $fecha || ! is_numeric($compra) || ! is_numeric($venta)) {
                return null;
            }

            return [
                'fecha' => $fecha->format('Y-m-d'),
                'compra' => (float) $compra,
                'venta' => (float) $venta,
            ];
        } catch (\Throwable $e) {
            Log::warning('No se pudo obtener el tipo de cambio de SUNAT: '.$e->getMessage());

            return null;
        }
    }
}
