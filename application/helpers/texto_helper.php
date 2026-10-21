<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Corrige texto UTF-8 que se guardó como si fuera Latin-1 (MÃ©xico, PresentaciÃ³n).
 */
function texto_sin_mojibake($texto) {
    if (!is_string($texto) || $texto === '' || !preg_match('/Ã.|Â./u', $texto)) {
        return $texto;
    }
    $reparado = @iconv('UTF-8', 'Windows-1252//IGNORE', $texto);
    if ($reparado === false || $reparado === '' || $reparado === $texto) {
        return $texto;
    }
    if (!mb_check_encoding($reparado, 'UTF-8') || preg_match('/Ã./u', $reparado)) {
        return $texto;
    }
    return $reparado;
}
