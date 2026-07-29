<?php
/**
 * @deprecated Usar run_seed_nominas_reset_demo.php (reset limpio + seed unificado).
 *
 * CLI:  php run_seed_nominas_meses_pasados.php apply|revert
 * Web:  /database/run_seed_nominas_meses_pasados.php?action=apply&key=chisa_demo_seed
 */
$action = $argv[1] ?? ($_GET['action'] ?? '');
$isCli  = (PHP_SAPI === 'cli');

if (!$isCli && ($_GET['key'] ?? '') !== 'chisa_demo_seed') {
    http_response_code(403);
    exit('Forbidden');
}

if (!in_array($action, ['apply', 'revert'], true)) {
    $msg = "DEPRECATED — use: php run_seed_nominas_reset_demo.php apply|revert\n";
    exit($isCli ? $msg : nl2br($msg));
}

if ($isCli) {
    fwrite(STDERR, "Aviso: run_seed_nominas_meses_pasados.php está obsoleto. Delegando a run_seed_nominas_reset_demo.php\n");
}

$script = __DIR__ . '/run_seed_nominas_reset_demo.php';
$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($action);
passthru($cmd, $code);
exit($code);
