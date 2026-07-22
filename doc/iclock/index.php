<?php
// Proxy local ZKTeco — enrutador ADMS (cdata / getrequest / devicecmd)

$metodo = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

header('Content-Type: text/plain');

// Enrutador inteligente
if (strpos($uri, 'cdata') !== false) {
    include 'ruta_cdata.php';
} elseif (strpos($uri, 'getrequest') !== false) {
    include 'ruta_getrequest.php';
} elseif (strpos($uri, 'devicecmd') !== false) {
    include 'ruta_devicecmd.php';
} else {
    // Por seguridad, si el reloj busca otra cosa, le decimos OK
    echo "OK";
}