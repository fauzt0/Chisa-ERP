<?php
/**
 * Verificación READ-ONLY para la presentación (iteración 3):
 *  - Migración final_alignment.sql aplicada o no / ENUMs de movimientos
 *  - Precios de productos (NULL / 0)
 *  - IVA en órdenes de venta y de compra
 *  - Impacto del pesaje OV-2026-0007 y residuo OB-00006
 * Uso: php doc/entrenamiento_3/tools/verificar_presentacion.php
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
$db = [];
require dirname(__DIR__, 3) . '/application/config/database.php';
$cfg = $db['default'];
mysqli_report(MYSQLI_REPORT_OFF);
$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($m->connect_error) { die("Conexion fallida: {$m->connect_error}\n"); }
$m->set_charset('utf8');
$DB = $cfg['database'];

function q($m, $sql, $label) {
    echo "\n=== $label ===\n";
    try { $r = $m->query($sql); } catch (Throwable $e) { echo 'ERROR: ' . $e->getMessage() . "\n"; return; }
    if (!$r) { echo "ERROR: {$m->error}\n"; return; }
    if ($r === true) { echo "ok\n"; return; }
    $n = 0;
    while ($row = $r->fetch_assoc()) {
        $n++;
        $parts = [];
        foreach ($row as $k => $v) { $parts[] = "$k=" . ($v === null ? 'NULL' : $v); }
        echo implode(' | ', $parts) . "\n";
    }
    if (!$n) echo "(sin filas)\n";
}

q($m, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$DB' AND TABLE_NAME='ordenes_venta'
       AND (COLUMN_NAME LIKE '%subtotal%' OR COLUMN_NAME LIKE '%iva%' OR COLUMN_NAME LIKE '%total%'
            OR COLUMN_NAME LIKE '%descuento%' OR COLUMN_NAME LIKE '%envio%')", 'A) Columnas de importes en ordenes_venta');

q($m, "SELECT id, folio, cliente_id, subtotal, iva, total, estatus, estatus_pago
       FROM ordenes_venta ORDER BY id DESC LIMIT 6", 'B) Órdenes de venta recientes (IVA)');

q($m, "SELECT COUNT(*) total_ov, SUM(iva>0) con_iva, SUM(total>0) con_total,
              SUM(subtotal>0) con_subtotal FROM ordenes_venta", 'C) OVs con IVA/total distinto de 0');

q($m, "SELECT id, folio, subtotal, iva, total, estatus FROM ordenes_compra ORDER BY id DESC LIMIT 4",
  'D) Órdenes de compra recientes (IVA)');

q($m, "SELECT id, folio, cliente_id, total, estatus, fecha_creacion FROM ordenes_venta WHERE id=26",
  'E) OV-2026-0007 (id 26) tras limpieza');

q($m, "SELECT id, folio, cliente_id, estatus FROM obras WHERE id=14 OR folio='OB-00006'",
  'F) OB-00006 (id 14): ¿sigue en tabla obras?');

q($m, "SELECT id, folio, estatus, total FROM obras ORDER BY id DESC LIMIT 5", 'F2) Últimas obras');

q($m, "SELECT id, insumo_id, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, referencia, fecha_movimiento
       FROM movimientos_inventario ORDER BY id DESC LIMIT 8", 'G) Últimos movimientos de insumos');

q($m, "SELECT i.id, i.codigo, i.nombre_tecnico, i.stock_actual FROM insumos i WHERE i.id IN (17,18,20)",
  'G2) Stock actual de los insumos del pesaje');

q($m, "SELECT COUNT(*) lotes FROM lotes_produccion", 'H) Lotes existentes');
q($m, "SELECT COUNT(*) movs_prod_tipo FROM movimientos_productos WHERE tipo_movimiento='Produccion'",
  'I) Movimientos de producto tipo Produccion');
