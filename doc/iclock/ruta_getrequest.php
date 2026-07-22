<?php
require_once __DIR__ . '/config.php';

$sn = $_GET['SN'] ?? 'DESCONOCIDO';

if (MODO_PRUEBA_LOCAL) {
    if (file_exists(ARCHIVO_COLA)) {
        $cola = json_decode(file_get_contents(ARCHIVO_COLA), true);
        if (is_array($cola) && count($cola) > 0) {
            $cmd = array_shift($cola);
            escribir_log(ARCHIVO_COLA, json_encode($cola, JSON_PRETTY_PRINT), false);
            echo 'C:' . $cmd['id'] . ':' . $cmd['cmd'] . "\n";
            exit;
        }
    }
    echo 'OK';
    exit;
}

require_once __DIR__ . '/lib_api.php';

$respuesta = hacer_peticion('comandos_pendientes/' . urlencode($sn), null, 'GET');
$json = json_decode($respuesta ?: '', true);

if (!empty($json['data']['comandos'])) {
    $cmd = $json['data']['comandos'][0];
    echo 'C:' . $cmd['id'] . ':' . $cmd['comando'] . "\n";
} else {
    echo 'OK';
}
exit;
