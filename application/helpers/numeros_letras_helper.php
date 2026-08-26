<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('numero_a_letras_mxn')) {
    /**
     * Convierte un monto a letra en formato mexicano.
     * Ej: 1740.00 → "UN MIL SETECIENTOS CUARENTA PESOS 00/100 M.N."
     */
    function numero_a_letras_mxn($monto)
    {
        $monto = round((float) $monto, 2);
        if ($monto < 0) {
            return 'MENOS ' . numero_a_letras_mxn(abs($monto));
        }

        $entero = (int) floor($monto);
        $centavos = (int) round(($monto - $entero) * 100);
        if ($centavos === 100) {
            $entero++;
            $centavos = 0;
        }

        $letras = _nl_entero_a_letras_es($entero);
        $sufijoPeso = ($entero === 1 && $centavos === 0) ? 'PESO' : 'PESOS';
        return $letras . ' ' . $sufijoPeso . ' ' . str_pad((string) $centavos, 2, '0', STR_PAD_LEFT) . '/100 M.N.';
    }
}

if (!function_exists('_nl_entero_a_letras_es')) {
    function _nl_entero_a_letras_es($n)
    {
        if ($n === 0) {
            return 'CERO';
        }

        $partes = [];

        $millones = (int) floor($n / 1000000);
        if ($millones > 0) {
            $partes[] = ($millones === 1)
                ? 'UN MILLON'
                : _nl_grupo_centenas($millones, false) . ' MILLONES';
            $n %= 1000000;
        }

        $miles = (int) floor($n / 1000);
        if ($miles > 0) {
            $partes[] = ($miles === 1)
                ? 'UN MIL'
                : _nl_grupo_centenas($miles, false) . ' MIL';
            $n %= 1000;
        }

        if ($n > 0) {
            $partes[] = _nl_grupo_centenas($n, true);
        }

        return implode(' ', $partes);
    }
}

if (!function_exists('_nl_grupo_centenas')) {
    function _nl_grupo_centenas($n, $es_final)
    {
        $centenas = [
            100 => 'CIEN', 200 => 'DOSCIENTOS', 300 => 'TRESCIENTOS', 400 => 'CUATROCIENTOS',
            500 => 'QUINIENTOS', 600 => 'SEISCIENTOS', 700 => 'SETECIENTOS', 800 => 'OCHOCIENTOS', 900 => 'NOVECIENTOS',
        ];
        $decenas = [
            10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE',
            16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
            20 => 'VEINTE', 30 => 'TREINTA', 40 => 'CUARENTA', 50 => 'CINCUENTA',
            60 => 'SESENTA', 70 => 'SETENTA', 80 => 'OCHENTA', 90 => 'NOVENTA',
        ];
        $unidades = [
            1 => 'UNO', 2 => 'DOS', 3 => 'TRES', 4 => 'CUATRO', 5 => 'CINCO',
            6 => 'SEIS', 7 => 'SIETE', 8 => 'OCHO', 9 => 'NUEVE',
        ];

        $texto = '';

        if ($n >= 100) {
            $c = (int) floor($n / 100) * 100;
            if ($c === 100 && $n === 100) {
                $texto = 'CIEN';
            } else {
                $texto = ($c === 100) ? 'CIENTO' : $centenas[$c];
            }
            $n %= 100;
        }

        if ($n >= 10 && $n <= 19) {
            $texto = trim($texto . ' ' . $decenas[$n]);
            return $texto;
        }

        if ($n >= 20) {
            $d = (int) floor($n / 10) * 10;
            $u = $n % 10;
            if ($u === 0) {
                $texto = trim($texto . ' ' . $decenas[$d]);
            } elseif ($d === 20) {
                $veintis = [1 => 'VEINTIUNO', 2 => 'VEINTIDOS', 3 => 'VEINTITRES', 4 => 'VEINTICUATRO', 5 => 'VEINTICINCO',
                    6 => 'VEINTISEIS', 7 => 'VEINTISIETE', 8 => 'VEINTIOCHO', 9 => 'VEINTINUEVE'];
                $texto = trim($texto . ' ' . ($es_final && $u === 1 ? 'VEINTIUN' : $veintis[$u]));
            } else {
                $texto = trim($texto . ' ' . $decenas[$d] . ' Y ' . ($es_final && $u === 1 ? 'UN' : $unidades[$u]));
            }
            return $texto;
        }

        if ($n > 0) {
            $texto = trim($texto . ' ' . ($es_final && $n === 1 ? 'UN' : $unidades[$n]));
        }

        return $texto;
    }
}
