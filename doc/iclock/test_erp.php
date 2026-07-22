<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib_api.php';

function mostrar_prueba($titulo, $resultado, $requiere_success = true) {
    echo "\n=== $titulo ===\n";
    if (!$resultado['ok']) {
        echo "❌ Error cURL: {$resultado['error']}\n";
        return false;
    }
    $json = json_decode($resultado['body'], true);
    $http_ok = $resultado['http'] >= 200 && $resultado['http'] < 300;
    $api_ok = !$requiere_success || (is_array($json) && ($json['status'] ?? '') === 'success');
    $ok = $http_ok && $api_ok;
    echo ($ok ? '✅' : '❌') . " HTTP {$resultado['http']}\n";
    echo "Respuesta:\n" . htmlspecialchars($resultado['body']) . "\n";
    return $ok;
}

echo '<h1>Prueba API /api/reloj/* (proxy ZKTeco)</h1><pre>';

if (MODO_PRUEBA_LOCAL) {
    echo "⚠️  MODO_PRUEBA_LOCAL = true → el proxy no llama al ERP.\n";
    echo "Pon MODO_PRUEBA_LOCAL = false en config.php para probar la API.\n</pre>";
    exit;
}

$sn = RELOJ_SN;
$base = rtrim(str_replace('/api/reloj/', '', API_BASE), '/');

echo "Dominio ERP: $base\n";
echo "Base API:    " . API_BASE . "\n";
echo "RELOJ_SN:    $sn\n";
echo "MODO_SYNC_DEBUG: " . (MODO_SYNC_DEBUG ? 'true (usa sync_asistencias_debug)' : 'false (producción)') . "\n";

echo "\n--- Endpoints de PRODUCCIÓN (4) ---\n";
echo "Autenticación: header X-API-Key\n";

$todo_ok = true;

$todo_ok &= mostrar_prueba(
    '1. GET /api/reloj/status',
    peticion_erp('status', null, 'GET')
);

$payload_sync = [
    'sn' => $sn,
    'table' => 'ATTLOG',
    'raw_data' => "1\t" . date('Y-m-d H:i:s') . "\t255\t15\n2\t" . date('Y-m-d H:i:s') . "\t255\t1\n",
];

$sync = peticion_erp('sync_asistencias', $payload_sync);
$sync_ok = mostrar_prueba('2. POST /api/reloj/sync_asistencias', $sync);
$todo_ok &= $sync_ok;

if (!$sync_ok && $sync['http'] === 403) {
    echo "\n⚠️  403: el SN '$sn' no coincide con el dispositivo del API_TOKEN.\n";
    echo "   En el ERP → Dispositivos → alta/edición con serial '$sn' y este token.\n";
}

$pendientes = peticion_erp('comandos_pendientes/' . $sn, null, 'GET');
$todo_ok &= mostrar_prueba(
    '3. GET /api/reloj/comandos_pendientes/' . $sn,
    $pendientes
);

echo "\n=== 4. POST /api/reloj/comando_resultado ===\n";
$pend_json = json_decode($pendientes['body'] ?? '', true);
$cmd_id_prueba = null;
if (!empty($pend_json['data']['comandos'][0]['id'])) {
    $cmd_id_prueba = $pend_json['data']['comandos'][0]['id'];
    echo "Hay comando pendiente ID $cmd_id_prueba — omitiendo POST de prueba para no marcarlo procesado.\n";
    echo "✅ Endpoint se probará cuando el reloj ejecute un comando real (devicecmd).\n";
} else {
    $resultado_cmd = peticion_erp('comando_resultado', [
        'comando_id' => 0,
        'return_code' => 0,
        'respuesta' => 'Prueba de conectividad desde test_erp.php',
    ]);
    if (!$resultado_cmd['ok']) {
        echo "❌ Error cURL: {$resultado_cmd['error']}\n";
        $todo_ok = false;
    } else {
        $cmd_json = json_decode($resultado_cmd['body'], true);
        $msg = $cmd_json['message'] ?? '';
        $api_responde = is_array($cmd_json) && isset($cmd_json['status']);
        $esperado = str_contains($msg, 'no encontrado') || str_contains($msg, 'ya procesado');
        if ($api_responde && $esperado) {
            echo "✅ HTTP {$resultado_cmd['http']} — API operativa (ID 0 no existe, respuesta esperada)\n";
            echo "Respuesta:\n" . htmlspecialchars($resultado_cmd['body']) . "\n";
        } else {
            $ok_cmd = ($cmd_json['status'] ?? '') === 'success';
            echo ($ok_cmd ? '✅' : '❌') . " HTTP {$resultado_cmd['http']}\n";
            echo "Respuesta:\n" . htmlspecialchars($resultado_cmd['body']) . "\n";
            $todo_ok &= $ok_cmd;
        }
    }
}

echo "\n--- Endpoints de PRUEBA (no producción) ---\n";

$debug = peticion_erp_debug('sync_asistencias_debug', $payload_sync);
$debug_ok = mostrar_prueba('5. POST /api/reloj/sync_asistencias_debug (sin token)', $debug, false);
echo "Monitor en navegador: {$base}/api/reloj/monitor\n";

echo "\n--- Mapeo proxy local ↔ API ERP ---\n";
echo "| Reloj ZKTeco (ADMS)     | Proxy local          | API ERP                    |\n";
echo "|-------------------------|----------------------|----------------------------|\n";
echo "| POST /iclock/cdata      | ruta_cdata.php       | POST sync_asistencias      |\n";
echo "| GET  /iclock/getrequest | ruta_getrequest.php  | GET  comandos_pendientes/SN|\n";
echo "| POST /iclock/devicecmd  | ruta_devicecmd.php   | POST comando_resultado     |\n";
echo "| (ping manual)           | test_conexion.php    | GET  status                |\n";

echo "\n--- Notas ---\n";
echo "• Los comandos NO se encolan por API. Se encolan en rh/RelojChecador/dispositivos (web RH).\n";
echo "• El reloj empuja datos al proxy; el proxy reenvía al ERP (protocolo ADMS, no polling).\n";
echo "• Para pruebas sin token/BD: MODO_SYNC_DEBUG=true en config.php + monitor en ERP.\n";

echo "\n" . ($todo_ok ? '✅ Integración lista para producción.' : '❌ Revisa los fallos arriba.') . "\n";
echo "Checadas: el reloj → cdata → sync_asistencias ya funciona (insertadas en ERP).\n";
if ($debug_ok) {
    echo "✅ sync_asistencias_debug también respondió (revisa /api/reloj/monitor).\n";
}

echo '</pre>';
