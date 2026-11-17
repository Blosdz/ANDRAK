<?php

namespace App\CoreFacturalo\Helpers;

class NumeroALetras
{
    private const UNIDADES = [
        '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ',
        'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE', 'VEINTE',
    ];

    private const DECENAS = [
        2 => 'VEINTI', 3 => 'TREINTA', 4 => 'CUARENTA', 5 => 'CINCUENTA',
        6 => 'SESENTA', 7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA',
    ];

    private const CENTENAS = [
        1 => 'CIENTO', 2 => 'DOSCIENTOS', 3 => 'TRESCIENTOS', 4 => 'CUATROCIENTOS', 5 => 'QUINIENTOS',
        6 => 'SEISCIENTOS', 7 => 'SETECIENTOS', 8 => 'OCHOCIENTOS', 9 => 'NOVECIENTOS',
    ];

    public static function convertir(float $numero, string $moneda = 'SOLES'): string
    {
        $entero = (int) floor($numero);
        $centavos = (int) round(($numero - $entero) * 100);

        $letras = $entero === 0 ? 'CERO' : self::convertirGrupo($entero);

        return sprintf('%s CON %02d/100 %s', trim($letras), $centavos, $moneda);
    }

    private static function convertirGrupo(int $numero): string
    {
        if ($numero === 100) {
            return 'CIEN';
        }

        if ($numero >= 1000000) {
            $millones = intdiv($numero, 1000000);
            $resto = $numero % 1000000;
            $prefijo = $millones === 1 ? 'UN MILLON' : self::convertirGrupo($millones).' MILLONES';

            return trim($prefijo.' '.self::convertirGrupo($resto));
        }

        if ($numero >= 1000) {
            $miles = intdiv($numero, 1000);
            $resto = $numero % 1000;
            $prefijo = $miles === 1 ? 'MIL' : self::convertirGrupo($miles).' MIL';

            return trim($prefijo.' '.self::convertirGrupo($resto));
        }

        if ($numero >= 100) {
            $centena = intdiv($numero, 100);
            $resto = $numero % 100;

            return trim(self::CENTENAS[$centena].' '.self::convertirGrupo($resto));
        }

        if ($numero <= 20) {
            return self::UNIDADES[$numero];
        }

        if ($numero < 30) {
            $unidad = $numero % 10;

            return $unidad === 0 ? 'VEINTE' : self::DECENAS[2].self::UNIDADES[$unidad];
        }

        $decena = intdiv($numero, 10);
        $unidad = $numero % 10;
        $texto = self::DECENAS[$decena];

        return $unidad === 0 ? $texto : $texto.' Y '.self::UNIDADES[$unidad];
    }
}
