<?php
/**
 * Ejecuta el seed de nóminas demo (CLI o navegador con clave).
 *
 * CLI:  php run_seed_nominas_demo.php apply
 *       php run_seed_nominas_demo.php revert
 * Web:  /database/run_seed_nominas_demo.php?action=apply&key=chisa_demo_seed
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
    $msg = "Uso: php run_seed_nominas_demo.php apply|revert\n";
    exit($isCli ? $msg : nl2br($msg));
}

require dirname(__DIR__) . '/application/config/database.php';
$cfg = $db['default'];

$mysqli = @new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($mysqli->connect_error) {
    exit('Error de conexión: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

if ($action === 'revert') {
    $sql = "
        DELETE nd FROM nominas_detalle nd
        INNER JOIN nominas n ON n.id = nd.nomina_id
        WHERE n.folio IN ('NOM000002','NOM000003','NOM000004','NOM000005');
        DELETE FROM nominas WHERE folio IN ('NOM000002','NOM000003','NOM000004','NOM000005');
        SELECT folio, estatus FROM nominas WHERE folio LIKE 'NOM%' ORDER BY folio;
    ";
} else {
    $file = __DIR__ . '/seed_nominas_demo.sql';
    $sql = file_get_contents($file);
    if ($sql === false) {
        exit('No se pudo leer seed_nominas_demo.sql');
    }
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
