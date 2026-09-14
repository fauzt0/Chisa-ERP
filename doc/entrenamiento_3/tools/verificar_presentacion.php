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

q($m, "SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
       WHERE TABLE_SCHEMA='$DB' AND (
         (TABLE_NAME='lotes_produccion' AND COLUMN_NAME IN ('orden_venta_id','obra_id','orden_produccion_id'))
         OR (TABLE_NAME IN ('ordenes_venta','obras') AND COLUMN_NAME='fecha_completado_produccion')
       ) ORDER BY TABLE_NAME, COLUMN_NAME", 'J) Columnas de alineación producción');

q($m, "SELECT TABLE_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
       WHERE TABLE_SCHEMA='$DB' AND COLUMN_NAME='tipo_movimiento'
         AND TABLE_NAME IN ('movimientos_productos','movimientos_inventario')", 'K) ENUMs tipo_movimiento');

q($m, "SELECT SUM(precio_venta IS NULL) nnull, SUM(precio_venta=0 OR precio_venta IS NULL) sin_precio,
              SUM(precio_venta>0) con_precio, COUNT(*) tot FROM productos", 'L) Conteos precio_venta');
q($m, "SELECT id, codigo, nombre, precio_venta FROM productos WHERE precio_venta>0 ORDER BY id LIMIT 20",
  'L2) Muestra productos con precio');

q($m, "SELECT
         SUM(descripcion LIKE 'Producto importado desde Excel%') placeholder,
         SUM(foto_producto IS NULL OR foto_producto='') sin_foto,
         SUM(rendimiento IS NULL OR rendimiento='') sin_rendimiento
       FROM productos", 'M) Placeholder / foto / rendimiento');

q($m, "SELECT COUNT(*) fabricados_sin_bom FROM productos p
       WHERE p.tipo_producto='Fabricado' AND p.estatus='Activo'
         AND NOT EXISTS (SELECT 1 FROM formulaciones f WHERE f.producto_id=p.id AND f.es_activa=1)",
  'M2) Fabricados activos sin formulación activa');
