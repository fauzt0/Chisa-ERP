<?php
/**
 * TEMPORAL — pruebas de la iteración 3 (Entrenamiento 3). SOLO LECTURA.
 * Se elimina al cerrar la iteración.
 *
 * Uso:
 *   php .../pruebas_entrenamiento3.php sql "SELECT ..."                  (solo SELECT)
 *   php .../pruebas_entrenamiento3.php route <uri> k=v ...               (endpoint por CLI, salida cruda)
 *   php .../pruebas_entrenamiento3.php render <uri>                      (render de vista: tamaño + errores)
 *   php .../pruebas_entrenamiento3.php bom <formulacion_id> <kg> [raw]   (explosión BOM resumida)
 */
$root = dirname(__DIR__, 3);
chdir($root);
$modo = $argv[1] ?? '';

function h_route(array $post, $uri) {
    $_POST  = $post;
    $_GET   = [];
    $_SERVER['argv']  = ['index.php', $uri];
    $_SERVER['argc']  = 2;
    ob_start();
    require dirname(__DIR__, 3) . '/index.php';
    return ob_get_clean();
}

if ($modo === 'sql') {
    define('BASEPATH', true);
    define('ENVIRONMENT', 'production');
    $db = [];
    require $root . '/application/config/database.php';
    $c = $db['default'];
    $m = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
    if ($m->connect_error) { fwrite(STDERR, "Conexion fallida\n"); exit(1); }
    $m->set_charset('utf8');
    $q = $argv[2] ?? '';
    if (!preg_match('/^\s*SELECT\b/i', $q)) { fwrite(STDERR, "Solo SELECT permitido\n"); exit(2); }
    $r = $m->query($q);
    if (!$r) { fwrite(STDERR, 'ERROR MySQL: ' . $m->error . "\n"); exit(3); }
    $cols = [];
    while ($f = $r->fetch_field()) { $cols[] = $f->name; }
    echo implode("\t", $cols) . "\n";
    while ($x = $r->fetch_row()) {
        echo implode("\t", array_map(function ($v) { return $v === null ? 'NULL' : $v; }, $x)) . "\n";
    }
    exit(0);
}

if ($modo === 'route') {
    $post = [];
    foreach (array_slice($argv, 3) as $kv) {
        $p = explode('=', $kv, 2);
        $post[$p[0]] = $p[1] ?? '';
    }
    $out = h_route($post, $argv[2] ?? '');
    echo $out;
    exit(0);
}

if ($modo === 'render') {
    $out = h_route([], $argv[2] ?? '');
    $fatales = preg_match_all('/Fatal error|Parse error|Uncaught/i', $out, $mf);
    $bloques = preg_match_all('/A PHP Error was encountered/i', $out);
    $deprec  = preg_match_all('/Severity:\s*8192/i', $out);
    preg_match('/<title>(.*?)<\/title>/is', $out, $t);
    printf("RENDER %s\n  bytes=%d  fatales=%d  php_errors=%d (deprecations=%d)\n", $argv[2] ?? '', strlen($out), $fatales, $bloques, $deprec);
    if ($fatales) { echo '  [' . implode(' | ', array_slice(array_unique($mf[0]), 0, 5)) . "]\n"; }
    if (isset($t[1])) { echo '  title: ' . trim(preg_replace('/\s+/', ' ', $t[1])) . "\n"; }
    exit($fatales > 0 ? 5 : 0);
}

if ($modo === 'bom') {
    $fid = (int)($argv[2] ?? 0);
    $kg  = (float)($argv[3] ?? 1);
    $raw = in_array('raw', array_slice($argv, 4), true);
    $out = h_route(['formulacion_id' => $fid, 'cantidad_kg' => $kg], 'produccion/Dashboard/explotar_bom_ajax');
    if ($raw) { echo $out; exit(0); }
    $json = json_decode(substr($out, strpos($out, '{')), true);
    if (!is_array($json) || empty($json['success'])) {
        echo "BOM form#$fid kg=$kg  →  RESPUESTA NO VÁLIDA\n" . substr($out, 0, 400) . "\n";
        exit(5);
    }
    $acc = ['nodos' => 0, 'fabricados' => 0, 'fabricados_sin_sub' => 0, 'hojas' => [], 'niveles' => []];
    $walk = function ($nodos) use (&$walk, &$acc) {
        foreach ($nodos as $n) {
            $acc['nodos']++;
            $acc['niveles'][$n['nivel']] = ($acc['niveles'][$n['nivel']] ?? 0) + 1;
            if (!empty($n['es_fabricado'])) {
                $acc['fabricados']++;
                if (empty($n['sub_componentes'])) {
                    $acc['fabricados_sin_sub']++;
                    $acc['cortes'][] = $n['nombre'] . ' (form#' . ($n['formulacion_sub_id'] ?? '?') . ')';
                } else {
                    $walk($n['sub_componentes']);
                }
            } else {
                $k = $n['insumo_id'] ?: 'prod' . $n['producto_id_comp'];
                $acc['hojas'][$k] = round(($acc['hojas'][$k] ?? 0) + (float)$n['kg'], 4);
            }
        }
    };
    $walk($json['arbol']);
    $plano_kg = 0; foreach ($json['plano'] as $p) { $plano_kg += (float)$p['kg']; }
    printf("BOM form#%d  kg=%s  →  nodos=%d  fabricados=%d (cortes por ciclo=%d)  hojas=%d  plano=%d (%.4f kg)\n",
        $fid, $kg, $acc['nodos'], $acc['fabricados'], $acc['fabricados_sin_sub'], count($acc['hojas']), count($json['plano']), $plano_kg);
    ksort($acc['niveles']);
    echo '  por nivel: ' . json_encode($acc['niveles']) . "\n";
    if (!empty($acc['cortes'])) { echo '  cortes: ' . implode(' | ', $acc['cortes']) . "\n"; }
    arsort($acc['hojas']);
    $top = array_slice($acc['hojas'], 0, 6, true);
    echo '  top kg (hojas): ' . json_encode($top) . "\n";
    exit(0);
}

fwrite(STDERR, "uso: sql | route | render | bom\n");
exit(4);
