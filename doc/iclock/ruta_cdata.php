<?php
// /var/www/html/iclock/ruta_cdata.php

require_once __DIR__ . '/config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

// 1. RESPUESTA DE INICIALIZACIÓN (Obligatorio para que el reloj no marque error)
if ($metodo === 'GET') {
    echo "RegistryCode=OK\nServerVersion=3.1.1\nServerName=CustomADMS\nPushVersion=2.4.1\nRefreshDelay=15\nPushOptionsVersion=1\nOK";
    exit;
}

// 2. RECEPCIÓN DE ASISTENCIAS
if ($metodo === 'POST') {
    $datos_crudos = file_get_contents('php://input');

    if (!empty($datos_crudos)) {
        if (MODO_PRUEBA_LOCAL) {
            // Modo local: guardar en datos_reloj.txt para panel.php
            $timestamp = date('Y-m-d H:i:s');
            $log_contenido = "==================================================\n";
            $log_contenido .= "[$timestamp] DATOS RECIBIDOS EN /cdata (Asistencias u Opciones)\n";
            $log_contenido .= "Ruta: $uri\n---------------- DATOS EN BRUTO ----------------\n";
            $log_contenido .= $datos_crudos . "\n==================================================\n\n";
            escribir_log(ARCHIVO_LOG, $log_contenido);
        } else {
            require_once __DIR__ . '/lib_api.php';

            $payload = [
                'sn' => $_GET['SN'] ?? RELOJ_SN,
                'table' => $_GET['table'] ?? 'ATTLOG',
                'raw_data' => $datos_crudos,
            ];

            $endpoint = MODO_SYNC_DEBUG ? 'sync_asistencias_debug' : 'sync_asistencias';
            if (MODO_SYNC_DEBUG) {
                peticion_erp_debug($endpoint, $payload);
            } else {
                hacer_peticion($endpoint, $payload);
            }
        }
    }

    // El reloj siempre necesita un "OK" para saber que el servidor recibió el paquete
    echo 'OK';
    exit;
}
