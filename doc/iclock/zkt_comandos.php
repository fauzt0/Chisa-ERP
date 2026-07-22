<?php
// Comandos ADMS ZKTeco — formato exacto para el reloj

if (!defined('PIN_ADMIN_RELOJ')) {
    require_once __DIR__ . '/config.php';
}

function pin_es_admin($pin) {
    return (int) $pin === (int) PIN_ADMIN_RELOJ;
}

function pin_borrable($pin) {
    $pin = (int) $pin;
    return $pin >= 2 && !pin_es_admin($pin);
}

/** Comando principal de borrado — formato exacto requerido */
function cmd_borrar_usuario($pin) {
    if (!pin_borrable($pin)) {
        return null;
    }
    return 'DATA DELETE USER PIN=' . (int) $pin;
}

/**
 * Secuencia para MB10-VL (facial): quitar plantilla y luego el usuario.
 * El comando final siempre es DATA DELETE USER PIN={id}
 */
function comandos_borrar_usuario($pin) {
    if (!pin_borrable($pin)) {
        return [];
    }
    $pin = (int) $pin;
    return [
        "DATA DELETE FACE PIN=$pin",
        "DATA DELETE USER PIN=$pin",
    ];
}

function cmd_alta_usuario($pin, $nombre, $pass = '') {
    $nombre = trim($nombre);
    return "DATA USER PIN=$pin\tName=$nombre\tPri=0\tPasswd=$pass\tCard=\tGrp=1\tTZ=0000000100000000\tVerify=0";
}
