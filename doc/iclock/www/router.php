<?php
/**
 * Router para el servidor embebido de PHP en Windows.
 * Mantiene la URL /iclock/cdata igual que con XAMPP (htdocs/iclock).
 *
 * Con Apache/XAMPP este archivo NO se usa; solo aplica a: php -S ... router.php
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base = realpath(__DIR__ . '/..');

if (preg_match('#^/iclock(/|$)#', $uri)) {
    $sub = '';
    if (preg_match('#^/iclock/(.+)$#', $uri, $m)) {
        $sub = $m[1];
    }

    // Rutas ADMS del reloj → index.php (mismo comportamiento que .htaccess + index.php)
    $es_ruta_reloj = ($sub === '' || $sub === 'cdata'
        || strpos($sub, 'cdata') !== false
        || strpos($sub, 'getrequest') !== false
        || strpos($sub, 'devicecmd') !== false);

    if (!$es_ruta_reloj && $sub !== '') {
        $archivo = $base . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sub);
        $real = realpath($archivo);
        if ($real && strpos($real, $base) === 0 && is_file($real) && strtolower(pathinfo($real, PATHINFO_EXTENSION)) === 'php') {
            chdir($base);
            require $real;
            return true;
        }
    }

    chdir($base);
    require $base . '/index.php';
    return true;
}

if ($uri === '/' || $uri === '') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "CHISA Proxy Reloj — OK\n";
    echo "Configure el reloj hacia: http://IP_DE_ESTA_PC/iclock/cdata\n";
    return true;
}

return false;
