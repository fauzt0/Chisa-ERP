<?php
/**
 * Ejecuta el seed de demo de Compras (CLI o navegador con clave).
 *
 * CLI:  php run_seed_demo_presentacion.php apply
 *       php run_seed_demo_presentacion.php revert
 * Web:  /database/run_seed_demo_presentacion.php?action=apply&key=chisa_demo_seed
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');

$action = $argv[1] ?? ($_GET['action'] ?? '');
$isCli = (PHP_SAPI === 'cli');

if (!$isCli) {
    if (($_GET['key'] ?? '') !== 'chisa_demo_seed') {
        http_response_code(403);
        exit('Forbidden');
    }
}

if (!in_array($action, ['apply', 'revert'], true)) {
    $msg = "Uso: php run_seed_demo_presentacion.php apply|revert\n";
    exit($isCli ? $msg : nl2br($msg));
}

require dirname(__DIR__) . '/application/config/database.php';
$cfg = $db['default'];

$mysqli = @new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($mysqli->connect_error) {
    exit('Error de conexión: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

$file = __DIR__ . ($action === 'apply'
    ? '/seed_demo_presentacion_compras.sql'
    : '/seed_demo_presentacion_compras_revert.sql');

$sql = file_get_contents($file);
if ($sql === false) {
    exit('No se pudo leer ' . basename($file));
}

if (!$mysqli->multi_query($sql)) {
    exit('Error al ejecutar seed: ' . $mysqli->error);
}

$results = [];
$errors = [];
$stmtCount = 0;

do {
    $stmtCount++;
    if ($result = $mysqli->store_result()) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $result->free();
    }
    if ($mysqli->errno) {
        $errors[] = $mysqli->error;
    }
} while ($mysqli->more_results() && $mysqli->next_result());

$output = [
    'action' => $action,
    'statements_executed' => $stmtCount,
    'errors' => $errors,
    'verification' => $results,
];

if ($isCli) {
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    exit(empty($errors) ? 0 : 1);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
