<?php
require_once __DIR__ . '/config.php';

$input = file_get_contents('php://input');
parse_str($input, $res);

if (MODO_PRUEBA_LOCAL) {
    $log = date('Y-m-d H:i:s') . " | ID={$res['ID']} | Return={$res['Return']} | RAW=" . trim($input) . "\n";
    escribir_log(ARCHIVO_LOG_CMD, $log);
    echo 'OK';
    exit;
}

require_once __DIR__ . '/lib_api.php';

$payload = [
    'comando_id' => $res['ID'],
    'return_code' => $res['Return'],
    'respuesta' => 'Ejecutado correctamente',
];

hacer_peticion('comando_resultado', $payload);
echo 'OK';
exit;
