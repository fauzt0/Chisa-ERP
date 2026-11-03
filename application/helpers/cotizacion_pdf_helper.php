<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extrae cabecera y partidas de un PDF de cotización HAPA (entrenamiento 6).
 */

if (!function_exists('cotizacion_pdf_unidad_patron')) {
    function cotizacion_pdf_unidad_patron() {
        return 'M2|M3|ML|MTS?|PZA\.?|KG|LT|LOTE|SERVICIO|SERV|JGO|KIT|GL|GAL';
    }
}

if (!function_exists('cotizacion_pdf_texto')) {
    function cotizacion_pdf_texto($path) {
        if (!is_file($path)) {
            return '';
        }
        $bin = trim((string) @shell_exec('command -v pdftotext'));
        if ($bin === '') {
            return '';
        }
        $cmd = $bin . ' -layout -enc UTF-8 ' . escapeshellarg($path) . ' - 2>/dev/null';
        $out = [];
        $code = 0;
        exec($cmd, $out, $code);
        return $code === 0 ? implode("\n", $out) : '';
    }
}

if (!function_exists('cotizacion_pdf_campo')) {
    function cotizacion_pdf_campo($texto, array $etiquetas) {
        foreach ($etiquetas as $eti) {
            if (!preg_match('/' . $eti . '\s*[:.]?\s*(.+)/iu', $texto, $m)) {
                continue;
            }
            $v = trim(preg_replace('/\s+/u', ' ', $m[1]));
            $partes = preg_split('/\s{2,}/u', $v);
            $v = trim($partes[0]);
            $v = preg_replace('/\s+(PRES\.?\s*REF|AT[\'´`’]?N|OBRA:|CONSTRUCTORA|CONTRUCTORA).*/iu', '', $v);
            $v = trim($v, " :-");
            if ($v !== '') {
                return mb_substr($v, 0, 200);
            }
        }
        return '';
    }
}

if (!function_exists('cotizacion_pdf_fecha')) {
    function cotizacion_pdf_fecha($texto) {
        $meses = [
            'ENERO' => '01', 'FEBRERO' => '02', 'MARZO' => '03', 'ABRIL' => '04',
            'MAYO' => '05', 'JUNIO' => '06', 'JULIO' => '07', 'AGOSTO' => '08',
            'SEPTIEMBRE' => '09', 'OCTUBRE' => '10', 'NOVIEMBRE' => '11', 'DICIEMBRE' => '12',
        ];
        if (!preg_match('/A\s+(\d{1,2})\s+DE\s+([A-ZÁÉÍÓÚ]+)\s+DEL?\s+(\d{4})/u', mb_strtoupper($texto, 'UTF-8'), $m)) {
            return date('Y-m-d');
        }
        $mes = $meses[$m[2]] ?? '01';
        return $m[3] . '-' . $mes . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('cotizacion_pdf_numeros_linea')) {
    function cotizacion_pdf_numeros_linea($linea) {
        preg_match_all('/[\d,]+\.\d{2}/', $linea, $m);
        $out = [];
        foreach ($m[0] as $n) {
            $out[] = (float) str_replace(',', '', $n);
        }
        return $out;
    }
}

if (!function_exists('cotizacion_pdf_es_cierre')) {
    function cotizacion_pdf_es_cierre($linea) {
        $t = mb_strtoupper(trim($linea), 'UTF-8');
        return (bool) preg_match('/^(SUB\s*-?\s*TOTAL|DESC\s*\d|I\.?\s*V\.?\s*A|IMPORTE DE PRESU|IMPORTE TOTAL|FORMA DE PAGO|ACEPTO|NOTA:|TOTAL\s+\$)/u', $t);
    }
}

if (!function_exists('cotizacion_pdf_es_grupo')) {
    function cotizacion_pdf_es_grupo($linea) {
        $t = trim($linea);
        if ($t === '' || mb_strlen($t) > 50) {
            return false;
        }
        if (preg_match('/[\d,]+\.\d{2}/', $t)) {
            return false;
        }
        if (preg_match('/SUMINISTRO|APLICACI|INCLUYE|DESMONTE|PREPARACI/iu', $t)) {
            return false;
        }
        return (bool) preg_match('/^(PLANTA|CUERPO|NIVEL|ZONA|AREA|ÁREA|FRENTE|LOTE|#)/iu', $t);
    }
}

if (!function_exists('cotizacion_pdf_partida_desde_bloque')) {
    function cotizacion_pdf_partida_desde_bloque($bloque, $grupo = '') {
        $unidad_re = cotizacion_pdf_unidad_patron();
        $lineas = preg_split("/\r\n|\n|\r/", $bloque);
        $ultima = '';
        foreach (array_reverse($lineas) as $ln) {
            $nums = cotizacion_pdf_numeros_linea($ln);
            if (count($nums) >= 2 && preg_match('/\b(?:' . $unidad_re . ')\b/iu', $ln)) {
                $ultima = $ln;
                break;
            }
        }
        $concepto = preg_replace('/^\s*\d+\.-/', '', $bloque);
        $unidad = 'M2';
        $cant = 0.0;
        $precio = 0.0;
        $importe = 0.0;
        if ($ultima !== '') {
            if (preg_match('/\b(' . $unidad_re . ')\b/iu', $ultima, $um)) {
                $unidad = strtoupper(str_replace('.', '', $um[1]));
                if ($unidad === 'MT' || $unidad === 'MTS') {
                    $unidad = 'M';
                }
            }
            $nums = cotizacion_pdf_numeros_linea($ultima);
            if (count($nums) >= 3) {
                $cant = $nums[0];
                $precio = $nums[1];
                $importe = $nums[2];
            } elseif (count($nums) === 2) {
                $cant = $nums[0];
                $precio = $nums[1];
                $importe = round($cant * $precio, 2);
            }
            $concepto = str_replace($ultima, ' ', $concepto);
        } elseif (preg_match('/HASTA\s+([\d,.]+)\s*(M2|M3|ML)\b/iu', $bloque, $hasta)) {
            $cant = (float) str_replace(',', '', $hasta[1]);
            $unidad = strtoupper($hasta[2]);
            if (preg_match('/IMPORTE[^\d$]*\$?\s*([\d,]+\.\d{2})/iu', $bloque, $imp)) {
                $importe = (float) str_replace(',', '', $imp[1]);
                $precio = $cant > 0 ? round($importe / $cant, 2) : $importe;
            }
        } elseif (preg_match('/IMPORTE[^\d$]*\$?\s*([\d,]+\.\d{2})/iu', $bloque, $imp)) {
            $unidad = 'LOTE';
            $cant = 1;
            $importe = (float) str_replace(',', '', $imp[1]);
            $precio = $importe;
        }
        $concepto = trim(preg_replace('/\s+/u', ' ', $concepto), " .-");
        $concepto = trim(preg_replace('/\b(?:' . $unidad_re . ')\b.*$/iu', '', $concepto), " .-");
        if (mb_strlen($concepto) < 8) {
            return null;
        }
        return [
            'grupo' => $grupo,
            'concepto' => mb_substr($concepto, 0, 4000),
            'unidad' => $unidad,
            'cantidad' => $cant,
            'precio_unitario' => $precio,
            'importe' => $importe,
        ];
    }
}

if (!function_exists('cotizacion_pdf_partidas_numeradas')) {
    function cotizacion_pdf_partidas_numeradas($texto) {
        $partes = preg_split('/(?=^\s*\d+\.-)/m', $texto);
        $out = [];
        foreach ($partes as $bloque) {
            if (!preg_match('/^\s*\d+\.-/', $bloque)) {
                continue;
            }
            $corte = preg_split('/^\s*(?:SUB\s*-?\s*TOTAL|IMPORTE DE PRESU|FORMA DE PAGO|ACEPTO|NOTA:)/mi', $bloque);
            $bloque = $corte[0];
            $p = cotizacion_pdf_partida_desde_bloque($bloque);
            if ($p) {
                $out[] = $p;
            }
        }
        return $out;
    }
}

if (!function_exists('cotizacion_pdf_partidas_libres')) {
    function cotizacion_pdf_partidas_libres($texto) {
        $unidad_re = cotizacion_pdf_unidad_patron();
        $lineas = preg_split("/\r\n|\n|\r/", $texto);
        $en = false;
        $buf = [];
        $grupo = '';
        $out = [];
        foreach ($lineas as $ln) {
            if (!$en) {
                if (preg_match('/CONCEPTO/i', $ln) && preg_match('/(UNIDAD|VOLUMEN|CANTIDAD|P\.?U\.?|IMPORTE)/i', $ln)) {
                    $en = true;
                }
                continue;
            }
            if (cotizacion_pdf_es_cierre($ln)) {
                break;
            }
            if (trim($ln) === '') {
                continue;
            }
            if (cotizacion_pdf_es_grupo($ln)) {
                $grupo = trim(preg_replace('/\s+/u', ' ', $ln));
                continue;
            }
            $nums = cotizacion_pdf_numeros_linea($ln);
            if (count($nums) >= 2 && preg_match('/\b(?:' . $unidad_re . ')\b/iu', $ln)) {
                $bloque = implode("\n", $buf) . "\n" . $ln;
                $p = cotizacion_pdf_partida_desde_bloque($bloque, $grupo);
                if ($p) {
                    $out[] = $p;
                }
                $buf = [];
            } else {
                $buf[] = $ln;
            }
        }
        return $out;
    }
}

if (!function_exists('cotizacion_pdf_parsear')) {
    function cotizacion_pdf_parsear($texto, $nombre_archivo = '') {
        $constructora = cotizacion_pdf_campo($texto, ['CONSTRUCTORA', 'CONTRUCTORA']);
        if ($constructora === '' && preg_match('/^\s*([A-ZÁÉÍÓÚÑ0-9][^\n]{6,160})\s*\n+\s*P\s+R\s+E\s+S\s+E\s+N\s+T\s+E/mu', $texto, $mc)) {
            $cand = trim(preg_replace('/\s+/u', ' ', $mc[1]));
            if (stripos($cand, 'CIUDAD DE') === false && stripos($cand, 'PRESENTAMOS') === false) {
                $constructora = mb_substr($cand, 0, 200);
            }
        }
        $atencion = cotizacion_pdf_campo($texto, ["AT['´`’]?N"]);
        $obra = cotizacion_pdf_campo($texto, ['OBRA']);
        $pres = cotizacion_pdf_campo($texto, ['PRES\.?\s*REF\.?']);
        if ($pres === '' && preg_match('/(\d{4,6})/', $nombre_archivo, $m)) {
            $pres = $m[1];
        }
        $flete = 0.0;
        if (preg_match('/FLETE[^\n$]*\$?\s*([\d,]+\.\d{2})/iu', $texto, $mf)) {
            $flete = (float) str_replace(',', '', $mf[1]);
        }
        $lineas = cotizacion_pdf_partidas_numeradas($texto);
        if (!$lineas) {
            $lineas = cotizacion_pdf_partidas_libres($texto);
        }
        if ($obra === '' && $nombre_archivo !== '') {
            $obra = trim(preg_replace('/\.pdf$/i', '', $nombre_archivo));
        }
        return [
            'constructora' => $constructora,
            'atencion' => $atencion,
            'obra' => $obra,
            'pres_ref' => $pres,
            'fecha' => cotizacion_pdf_fecha($texto),
            'flete' => $flete,
            'introduccion' => 'PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.',
            'lineas' => $lineas,
        ];
    }
}
