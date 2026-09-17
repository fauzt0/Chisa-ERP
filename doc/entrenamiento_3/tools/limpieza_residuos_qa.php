<?php
/**
 * TEMPORAL — limpieza de residuos QA (2026-09-17)
 *
 * Uso:
 *   php doc/entrenamiento_3/tools/limpieza_residuos_qa.php            # dry-run (default)
 *   php doc/entrenamiento_3/tools/limpieza_residuos_qa.php --apply
 *
 * Acciones (idempotentes, puntuales con WHERE id):
 *   1. OB-00006 (obras.id=14, activo=0) -> estatus='Cancelada'
 *   2. PESAJE-venta-26 (OV-2026-0007 cancelada) -> movimientos de Entrada que restauran
 *      #18 (0.03) y #20 (0.97) con referencia REVERSO-QA-PESAJE-venta-26.
 *      El trigger trg_stock_insumos_movimiento actualiza insumos.stock_actual.
 *   No borra nada: OV-TEST-001 / OV-2026-0004 / cliente "Empresa de Prueba" se conservan.
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
$db = [];
require dirname(__DIR__, 3) . '/application/config/database.php';
$cfg = $db['default'];

$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($m->connect_error) { die("Conexion fallida: {$m->connect_error}\n"); }
$m->set_charset('utf8');

$apply = in_array('--apply', $argv, true);
$ref   = 'REVERSO-QA-PESAJE-venta-26';
$rev   = [18 => 0.03, 20 => 0.97];

echo $apply ? "== MODO APPLY ==\n" : "== MODO DRY-RUN ==\n";

$obra = $m->query("SELECT id, folio, estatus, activo FROM obras WHERE id=14")->fetch_assoc();
echo "OB-00006: " . json_encode($obra) . "\n";
foreach ($rev as $iid => $q) {
    $r = $m->query("SELECT id, stock_actual FROM insumos WHERE id=$iid")->fetch_assoc();
    echo "Insumo #{$iid}: stock_actual={$r['stock_actual']} (reverso +{$q})\n";
}
$nrev = (int) $m->query("SELECT COUNT(*) c FROM movimientos_inventario WHERE referencia='$ref'")->fetch_assoc()['c'];
echo "Reversos previos con referencia '$ref': $nrev\n";

if (!$apply) {
    echo "\n(dry-run: no se escribio nada; usar --apply para ejecutar)\n";
    exit;
}

$m->begin_transaction();
try {
    $ok = $m->query("UPDATE obras SET estatus='Cancelada' WHERE id=14 AND activo=0 AND estatus<>'Cancelada'");
    if (!$ok) { throw new RuntimeException($m->error); }
    echo "obras actualizadas: " . $m->affected_rows . "\n";

    if ($nrev === 0) {
        foreach ($rev as $iid => $q) {
            $r  = $m->query("SELECT stock_actual, precio_promedio FROM insumos WHERE id=$iid")->fetch_assoc();
            $sa = (float) $r['stock_actual'];
            $sn = $sa + $q;
            $cu = (float) $r['precio_promedio'];
            $ct = round($q * $cu, 2);
            $sql = "INSERT INTO movimientos_inventario
                    (insumo_id, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, costo_unitario, costo_total, motivo, referencia, fecha_movimiento, usuario_id)
                    VALUES ($iid, 'Entrada', $q, $sa, $sn, $cu, $ct,
                            'Reverso QA pesaje OV-2026-0007 (cancelada)', '$ref', NOW(), 1)";
            if (!$m->query($sql)) { throw new RuntimeException($m->error); }
            echo "reverso insumo #$iid: +$q (stock $sa -> $sn), movimiento id {$m->insert_id}\n";
        }
    } else {
        echo "reversos ya existian: no se duplican\n";
    }
    $m->commit();
    echo "COMMIT OK\n";
} catch (Throwable $e) {
    $m->rollback();
    echo "ROLLBACK: {$e->getMessage()}\n";
    exit(1);
}

$obra = $m->query("SELECT id, folio, estatus, activo FROM obras WHERE id=14")->fetch_assoc();
echo "OB-00006 final: " . json_encode($obra) . "\n";
foreach ($rev as $iid => $q) {
    $r = $m->query("SELECT id, stock_actual FROM insumos WHERE id=$iid")->fetch_assoc();
    echo "Insumo #{$iid} final: stock_actual={$r['stock_actual']}\n";
}
