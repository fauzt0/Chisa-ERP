<?php
// Forzar a PHP a mostrar todos los errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Prueba de Conexión al ERP</h1>";

// Validamos si curl existe
if (!function_exists('curl_init')) {
    die("<h3 style='color:red;'>ERROR FATAL: La extensión php-curl no está instalada o activa en Fedora.</h3>");
}

include 'lib_api.php';

echo "<pre>";
echo "Iniciando petición a: " . API_BASE . "status\n";

$ch = curl_init(API_BASE . 'status');
$headers = ['X-API-Key: ' . API_TOKEN, 'Content-Type: application/json'];

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$respuesta = curl_exec($ch);

if(curl_errno($ch)){
    echo "❌ Error en cURL: " . curl_error($ch) . "\n\n";
    echo "--- Diagnóstico ---\n";
    echo "PHP SAPI: " . php_sapi_name() . "\n";
    echo "Usuario PHP: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user()) . "\n";

    $sebool = @shell_exec('getsebool httpd_can_network_connect 2>/dev/null');
    if ($sebool !== null && $sebool !== '') {
        echo "SELinux httpd_can_network_connect: " . trim($sebool) . "\n";
        if (strpos($sebool, 'off') !== false && php_sapi_name() !== 'cli') {
            echo "\n⚠️  Apache/PHP-FPM no puede hacer conexiones salientes con SELinux en 'off'.\n";
            echo "Solución (Fedora/RHEL):\n";
            echo "  sudo setsebool -P httpd_can_network_connect 1\n";
        }
    }
} else {
    echo "✅ Respuesta del servidor:\n";
    echo htmlspecialchars($respuesta);
}

curl_close($ch);
echo "</pre>";
?>