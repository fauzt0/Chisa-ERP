<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

echo "<h1>Validación del Proxy ZKTeco</h1><pre>";

$ok = true;

function check($label, $pass, $detail = '') {
    global $ok;
    if (!$pass) $ok = false;
    echo ($pass ? '✅' : '❌') . " $label";
    if ($detail !== '') echo " — $detail";
    echo "\n";
}

// 1. Extensión cURL
check('php-curl', function_exists('curl_init'));

// 1b. Certificado CA para HTTPS (Windows)
if (PHP_OS_FAMILY === 'Windows') {
    $cacert_path = ini_get('curl.cainfo');
    $cacert_exists = $cacert_path && file_exists($cacert_path);
    check('cacert.pem configurado', $cacert_exists, 
        $cacert_exists ? "$cacert_path" : 
        'Falta curl.cainfo en php.ini. Ejecuta configurar_entorno.bat o ver Anexo B en GUIA_INSTALACION.md');
}

// 2. Modo actual
echo "\n--- Configuración ---\n";
echo 'MODO_PRUEBA_LOCAL: ' . (MODO_PRUEBA_LOCAL ? 'true (local)' : 'false (ERP remoto)') . "\n";
echo 'MODO_SYNC_DEBUG:   ' . (MODO_SYNC_DEBUG ? 'true (sync_asistencias_debug)' : 'false (sync_asistencias prod)') . "\n";
echo 'RELOJ_SN:          ' . RELOJ_SN . "\n";

// 3. Permisos de escritura (Apache debe poder escribir)
$archivos = [
    'comandos.json' => ARCHIVO_COLA,
    'datos_reloj.txt' => ARCHIVO_LOG,
    'log_comandos.txt' => ARCHIVO_LOG_CMD,
];

echo "\n--- Permisos de archivos ---\n";
foreach ($archivos as $nombre => $ruta) {
    if (!file_exists($ruta)) {
        @touch($ruta);
    }
    $probe = @file_put_contents($ruta, file_exists($ruta) ? file_get_contents($ruta) : '', LOCK_EX);
    check("Escritura en $nombre", $probe !== false, $probe === false ? 'Apache no puede escribir — ver solución abajo' : 'OK');
}

// 4. Rutas del reloj
echo "\n--- Rutas HTTP ---\n";
$base = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME']);
$base = rtrim($base, '/');
echo "cdata:       $base/cdata\n";
echo "getrequest:  $base/getrequest\n";
echo "devicecmd:   $base/devicecmd\n";

// 5. Conexión ERP (solo en modo remoto)
if (!MODO_PRUEBA_LOCAL) {
    echo "\n--- Conexión ERP ---\n";
    require_once __DIR__ . '/lib_api.php';
    $ch = curl_init(API_BASE . 'status');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['X-API-Key: ' . API_TOKEN],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    check('API ERP /status', $resp !== false && $err === '', $err ?: substr($resp, 0, 120));

    $payload = [
        'sn' => RELOJ_SN,
        'table' => 'ATTLOG',
        'raw_data' => "1\t" . date('Y-m-d H:i:s') . "\t255\t15\t0\t0\t0\t0\t0\t0\n",
    ];
    $sync = peticion_erp('sync_asistencias', $payload);
    $sync_json = json_decode($sync['body'] ?? '', true);
    check(
        'API ERP sync_asistencias',
        $sync['ok'] && $sync['http'] >= 200 && $sync['http'] < 300 && ($sync_json['status'] ?? '') === 'success',
        $sync['error'] ?: 'HTTP ' . $sync['http'] . ' — ' . substr($sync['body'], 0, 100)
    );
    echo "Prueba completa: /iclock/test_erp.php\n";
} else {
    echo "\n--- Conexión ERP ---\n";
    echo "⏭️  Omitida (modo local activo)\n";
}

if (!$ok) {
    echo "\n⚠️  SOLUCIÓN PERMISOS:\n";
    if (PHP_OS_FAMILY === 'Windows') {
        echo "  Ejecute iclock\\windows\\configurar_entorno.bat como administrador.\n";
        echo "  Verifique que la carpeta iclock no esté en una ruta protegida (ej. Program Files).\n";
        echo "  Si persiste: Propiedades de la carpeta iclock → Seguridad → permitir escritura al usuario que ejecuta el proxy.\n";
    } else {
        echo "  sudo chown apache:apache /var/www/html/iclock/comandos.json /var/www/html/iclock/datos_reloj.txt /var/www/html/iclock/log_comandos.txt\n";
        echo "  sudo chcon -t httpd_sys_rw_content_t /var/www/html/iclock/comandos.json /var/www/html/iclock/datos_reloj.txt /var/www/html/iclock/log_comandos.txt\n";
    }
}

echo "\n" . ($ok ? '✅ Todo listo para pruebas.' : '❌ Corrige los puntos marcados antes de producción.') . "\n";
echo '</pre>';
