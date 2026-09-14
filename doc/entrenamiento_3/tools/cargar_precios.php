<?php
/**
 * Carga precio_venta desde productos_match.json (lista 2025, sin IVA).
 * Default: --dry-run. Escribir: --apply
 * Regla presentación: CUBETA si existe; si no, la primera.
 * No toca ids 3, 4, 475 si ya tienen precio_venta > 0.
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
$db = [];
require dirname(__DIR__, 3) . '/application/config/database.php';
$cfg = $db['default'];
$apply = in_array('--apply', $argv, true);
$jsonPath = dirname(__DIR__) . '/manifiestos/productos_match.json';
$items = json_decode(file_get_contents($jsonPath), true);
if (!is_array($items)) { fwrite(STDERR, "JSON inválido\n"); exit(1); }

$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($m->connect_error) { die("Conexion: {$m->connect_error}\n"); }
$m->set_charset('utf8');

$protegidos = [3, 4, 475];
$cargados = 0;
$omitidos = 0;
$sinMatch = 0;
$filas = [];

function norm($s) {
    $s = mb_strtoupper((string) $s, 'UTF-8');
    $s = strtr($s, ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N']);
    return preg_replace('/[^A-Z0-9]+/', '', $s);
}

function pick_presentacion(array $pres) {
    foreach ($pres as $p) {
        if (isset($p['presentacion']) && strtoupper($p['presentacion']) === 'CUBETA') {
            return $p;
        }
    }
    return $pres[0];
}

function find_producto(mysqli $m, array $item) {
    if (!empty($item['producto_id']) && ctype_digit((string) $item['producto_id'])) {
        $id = (int) $item['producto_id'];
        $r = $m->query("SELECT id, codigo, nombre, precio_venta FROM productos WHERE id = $id");
        $row = $r ? $r->fetch_assoc() : null;
        if ($row) { return $row; }
    }
    $nombres = array_filter([
        $item['producto_nombre_bd'] ?? null,
        $item['ocr_nombre'] ?? null,
    ]);
    foreach ($nombres as $nom) {
        $esc = $m->real_escape_string($nom);
        $r = $m->query("SELECT id, codigo, nombre, precio_venta FROM productos WHERE nombre = '$esc' LIMIT 1");
        $row = $r ? $r->fetch_assoc() : null;
        if ($row) { return $row; }
    }
    $target = norm($item['ocr_nombre'] ?? '');
    if ($target === '') { return null; }
    $r = $m->query("SELECT id, codigo, nombre, precio_venta FROM productos");
    while ($row = $r->fetch_assoc()) {
        if (norm($row['nombre']) === $target || norm($row['codigo']) === $target) {
            return $row;
        }
    }
    return null;
}

echo $apply ? "MODO APPLY\n" : "MODO DRY-RUN (nada se escribe)\n";
echo "Regla: presentación CUBETA; si no hay, primera de presentaciones[].\n";
echo str_pad('id', 6) . str_pad('codigo', 28) . str_pad('antes', 12) . str_pad('despues', 12) . "accion\n";

foreach ($items as $item) {
    $pres = $item['presentaciones'] ?? [];
    if (!is_array($pres) || count($pres) === 0) {
        continue;
    }
    $p = pick_presentacion($pres);
    $precio = isset($p['precio_sin_iva']) ? (float) $p['precio_sin_iva'] : 0;
    if ($precio <= 0) {
        $omitidos++;
        continue;
    }
    $prod = find_producto($m, $item);
    if (!$prod) {
        $sinMatch++;
        echo "----  SIN MATCH  " . ($item['ocr_nombre'] ?? '') . "  precio=$precio\n";
        continue;
    }
    $id = (int) $prod['id'];
    $antes = $prod['precio_venta'] === null ? null : (float) $prod['precio_venta'];
    $accion = 'update';
    if (in_array($id, $protegidos, true) && $antes !== null && $antes > 0) {
        $accion = 'skip_protegido';
        $omitidos++;
    } elseif ($antes !== null && abs($antes - $precio) < 0.005) {
        $accion = 'igual';
        $omitidos++;
    } else {
        if ($apply) {
            $st = $m->prepare('UPDATE productos SET precio_venta = ? WHERE id = ?');
            $st->bind_param('di', $precio, $id);
            $st->execute();
            $st->close();
        }
        $cargados++;
    }
    $filas[] = [$id, $prod['codigo'], $antes, $precio, $accion, $p['presentacion'] ?? ''];
    printf("%-6d%-28s%-12s%-12s%s (%s)\n",
        $id,
        substr($prod['codigo'], 0, 27),
        $antes === null ? 'NULL' : number_format($antes, 2, '.', ''),
        number_format($precio, 2, '.', ''),
        $accion,
        $p['presentacion'] ?? ''
    );
}

$r = $m->query("SELECT SUM(precio_venta IS NULL) nnull, SUM(precio_venta = 0) ncero, SUM(precio_venta > 0) npos, COUNT(*) tot FROM productos");
$c = $r->fetch_assoc();
echo "\nResumen: cargados=$cargados omitidos=$omitidos sin_match=$sinMatch apply=" . ($apply ? '1' : '0') . "\n";
echo "productos tot={$c['tot']} precio>0={$c['npos']} =0={$c['ncero']} NULL={$c['nnull']}\n";
