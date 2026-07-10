<?php
/**
 * Ejecuta la migración obras_orden_venta_link.sql una sola vez.
 * Uso: php database/run_obras_migration.php
 */
$configFile = dirname(__DIR__) . '/application/config/database.php';
if (!file_exists($configFile)) {
    fwrite(STDERR, "No se encontró database.php\n");
    exit(1);
}

define('BASEPATH', 'migration');
define('ENVIRONMENT', 'production');
require $configFile;

$db = $db['default'];
$mysqli = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database']);
if ($mysqli->connect_error) {
    fwrite(STDERR, "Error de conexión: " . $mysqli->connect_error . "\n");
    exit(1);
}

$sqlFile = __DIR__ . '/obras_orden_venta_link.sql';
$sql = file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "No se pudo leer el archivo SQL\n");
    exit(1);
}

if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

if ($mysqli->error) {
    fwrite(STDERR, "Error SQL: " . $mysqli->error . "\n");
    exit(1);
}

echo "Migración obras_orden_venta_link ejecutada correctamente.\n";
$mysqli->close();
