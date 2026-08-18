<?php
/**
 * Script de validación del módulo Ventas (ejecutar: php doc/test_ventas_module.php)
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
$db = [];
require dirname(__DIR__) . '/application/config/database.php';
$cfg = $db['default'];

$mysqli = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error . "\n");
}

$ok = 0;
$fail = 0;
$warn = 0;

function test($name, $pass, $detail = '') {
    global $ok, $fail;
    if ($pass) {
        $ok++;
        echo "[OK] $name\n";
    } else {
        $fail++;
        echo "[FAIL] $name" . ($detail ? " — $detail" : "") . "\n";
    }
}

function warn($name, $detail = '') {
    global $warn;
    $warn++;
    echo "[WARN] $name" . ($detail ? " — $detail" : "") . "\n";
}

echo "=== VALIDACIÓN MÓDULO VENTAS ===\n\n";

// Tablas requeridas
$tables = [
    'clientes', 'ordenes_venta', 'detalle_orden_venta', 'pagos_ordenes',
    'descuentos', 'solicitudes_produccion', 'facturas', 'obras',
    'seguimientos_cliente', 'productos', 'movimientos_inventario',
];
foreach ($tables as $t) {
    $r = $mysqli->query("SHOW TABLES LIKE '$t'");
    test("Tabla $t existe", $r && $r->num_rows > 0);
}

// Columnas CRM nuevas
$r = $mysqli->query("SHOW COLUMNS FROM clientes LIKE 'tipo_contacto'");
test("Columna clientes.tipo_contacto", $r && $r->num_rows > 0);

$r = $mysqli->query("SHOW COLUMNS FROM descuentos LIKE 'cliente_id'");
test("Columna descuentos.cliente_id", $r && $r->num_rows > 0);

// Triggers de totales OV
$r = $mysqli->query("SHOW TRIGGERS LIKE 'detalle_orden_venta'");
$triggers = [];
while ($row = $r->fetch_assoc()) {
    $triggers[] = $row['Trigger'];
}
test("Trigger tr_actualizar_totales_ov_insert", in_array('tr_actualizar_totales_ov_insert', $triggers));
test("Trigger tr_detalle_ov_subtotal_insert", in_array('tr_detalle_ov_subtotal_insert', $triggers));

// Folios stored procedures
$procs = ['sp_generar_folio_orden_venta', 'sp_generar_folio_pago', 'sp_generar_folio_solicitud_produccion'];
foreach ($procs as $p) {
    $r = $mysqli->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = '$p'");
    test("Procedimiento $p", $r && $r->num_rows > 0);
}

// Integración producción
$r = $mysqli->query("SELECT COUNT(*) AS c FROM solicitudes_produccion sp JOIN ordenes_venta ov ON ov.id = sp.orden_venta_id");
$row = $r->fetch_assoc();
test("Solicitudes producción vinculadas a órdenes", true, $row['c'] . ' registros');

// Cliente mostrador
$r = $mysqli->query("SELECT id FROM clientes WHERE codigo = 'CLI-00000'");
test("Cliente MOSTRADOR (CLI-00000)", $r && $r->num_rows > 0);

// tipo_cliente enum coherente
$r = $mysqli->query("SHOW COLUMNS FROM clientes LIKE 'tipo_cliente'");
$row = $r->fetch_assoc();
$enum = $row['Type'] ?? '';
test("tipo_cliente enum incluye Empresa", strpos($enum, 'Empresa') !== false, $enum);

// Órdenes con totales calculados
$r = $mysqli->query("
    SELECT COUNT(*) AS bad FROM ordenes_venta ov
    WHERE ov.estatus != 'Cancelada'
    AND ov.subtotal > 0
    AND (SELECT COALESCE(SUM(subtotal),0) FROM detalle_orden_venta WHERE orden_venta_id = ov.id) != ov.subtotal
");
$row = $r->fetch_assoc();
test("Totales OV coherentes con detalle", (int)$row['bad'] === 0, (int)$row['bad'] . ' órdenes inconsistentes');

// Estatus de órdenes
$r = $mysqli->query("SELECT estatus, COUNT(*) c FROM ordenes_venta GROUP BY estatus");
echo "\n--- Distribución estatus órdenes ---\n";
while ($row = $r->fetch_assoc()) {
    echo "  {$row['estatus']}: {$row['c']}\n";
}

// Obras vinculadas
if ($mysqli->query("SHOW COLUMNS FROM obras LIKE 'orden_venta_id'")->num_rows) {
    $r = $mysqli->query("SELECT COUNT(*) c FROM obras WHERE orden_venta_id IS NOT NULL");
    $row = $r->fetch_assoc();
    echo "\n--- Obras con orden de venta: {$row['c']} ---\n";
} else {
    warn("obras.orden_venta_id no existe");
}

// Archivos PHP del módulo
$files = [
    'application/controllers/ventas/Pos.php',
    'application/controllers/ventas/Ordenes.php',
    'application/controllers/ventas/Clientes.php',
    'application/controllers/ventas/ObrasVentas.php',
    'application/controllers/ventas/Descuentos.php',
    'application/models/Ventas/VentasModel.php',
    'application/models/Ventas/ClientesModel.php',
];
$base = dirname(__DIR__);
foreach ($files as $f) {
    $path = $base . '/' . $f;
    test("Archivo $f", file_exists($path));
    if (file_exists($path)) {
        exec("php -l " . escapeshellarg($path), $out, $code);
        test("Sintaxis $f", $code === 0);
    }
}

echo "\n=== RESUMEN: OK=$ok FAIL=$fail WARN=$warn ===\n";
$mysqli->close();
exit($fail > 0 ? 1 : 0);
