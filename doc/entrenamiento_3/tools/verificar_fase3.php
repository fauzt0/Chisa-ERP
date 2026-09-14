<?php
/**
 * TEMPORAL — verificación post-Fase 3 (solo lectura). Se elimina al cerrar la iteración.
 * Uso: php doc/entrenamiento_3/tools/verificar_fase3.php
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
$db = [];
require dirname(__DIR__, 3) . '/application/config/database.php';
$cfg = $db['default'];
$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($m->connect_error) { die("Conexion fallida\n"); }
$m->set_charset('utf8');

echo "=== 1. Formulaciones nuevas (id >= 956) ===\n";
$r = $m->query("SELECT f.id, f.producto_id, p.nombre, f.version, f.nombre_version, f.es_activa,
                       f.cantidad_producida, f.rendimiento_m2_por_kg, f.comentarios,
                       (SELECT COUNT(*) FROM detalle_formulacion d WHERE d.formulacion_id = f.id) comps,
                       (SELECT ROUND(SUM(d.porcentaje),2) FROM detalle_formulacion d WHERE d.formulacion_id = f.id) suma_pct,
                       (SELECT ROUND(SUM(d.cantidad),3) FROM detalle_formulacion d WHERE d.formulacion_id = f.id) suma_kg,
                       (SELECT COUNT(DISTINCT COALESCE(d.grupo_color,'-')) FROM detalle_formulacion d WHERE d.formulacion_id = f.id) grupos
                FROM formulaciones f JOIN productos p ON p.id = f.producto_id
                WHERE f.id >= 956 ORDER BY f.id");
while ($x = $r->fetch_assoc()) {
    printf("#%d prod#%-4d %-38s v%s activa=%s lote=%s comps=%d sum_pct=%s sum_kg=%s grupos=%d m2kg=%s\n",
        $x['id'], $x['producto_id'], $x['nombre'], $x['version'], $x['es_activa'], $x['cantidad_producida'],
        $x['comps'], $x['suma_pct'], $x['suma_kg'], $x['grupos'], (string)$x['rendimiento_m2_por_kg']);
    echo "      " . $x['comentarios'] . "\n";
}

echo "\n=== 2. T-034 (producto #225, form nueva): detalle por grupo ===\n";
$fid = 0;
$r = $m->query("SELECT id FROM formulaciones WHERE producto_id = 225 ORDER BY id DESC LIMIT 1");
$fid = (int)$r->fetch_assoc()['id'];
$g = null;
$r = $m->query("SELECT df.grupo_color, df.cantidad, df.porcentaje, df.porcentaje_fase_acuosa, df.kg_fase_acuosa,
                       df.orden, i.nombre_tecnico
                FROM detalle_formulacion df JOIN insumos i ON i.id = df.insumo_id
                WHERE df.formulacion_id = $fid ORDER BY COALESCE(df.grupo_color,''), df.orden");
$tot = [];
while ($x = $r->fetch_assoc()) {
    if ($x['grupo_color'] !== $g) { $g = $x['grupo_color']; echo "-- grupo: " . ($g ?: '(sin grupo)') . "\n"; }
    printf("   %-38s %8s Kg  %6s%%  pct_fase=%s  kg_fase=%s\n",
        $x['nombre_tecnico'], $x['cantidad'], $x['porcentaje'],
        (string)$x['porcentaje_fase_acuosa'], (string)$x['kg_fase_acuosa']);
    $tot[$g]['kg'] = round(($tot[$g]['kg'] ?? 0) + (float)$x['cantidad'], 4);
}
foreach ($tot as $k => $v) { echo "-- grupo " . ($k ?: '(sin grupo)') . ": total {$v['kg']} Kg\n"; }
echo "-- suma total: " . round(array_sum(array_column($tot, 'kg')), 4) . " Kg (lote 19.35)\n";

echo "\n=== 3. Invariante: una sola activa por producto afectado ===\n";
$r = $m->query("SELECT f.producto_id, p.nombre, SUM(f.es_activa=1) activas, COUNT(*) total
                FROM formulaciones f JOIN productos p ON p.id = f.producto_id
                WHERE f.producto_id IN (202,210,211,212,213,215,223,224,225,475,476)
                GROUP BY f.producto_id, p.nombre ORDER BY f.producto_id");
while ($x = $r->fetch_assoc()) {
    printf("prod#%-4d %-34s activas=%d total=%d %s\n", $x['producto_id'], $x['nombre'], $x['activas'], $x['total'],
        ((int)$x['activas'] === 1) ? 'OK' : '*** REVISAR ***');
}

echo "\n=== 4. Productos e insumos nuevos ===\n";
$r = $m->query("SELECT id, codigo, nombre, categoria_id, unidad_venta, contenido_neto, precio_venta, estatus
                FROM productos WHERE id >= 475 ORDER BY id");
while ($x = $r->fetch_assoc()) {
    printf("#%d [%s] %-38s cat=%s %s/%s precio=%s %s\n", $x['id'], $x['codigo'], $x['nombre'],
        $x['categoria_id'], (string)$x['unidad_venta'], (string)$x['contenido_neto'], (string)$x['precio_venta'], $x['estatus']);
}
$r = $m->query("SELECT id, codigo, nombre_tecnico, tipo, producto_id, unidad_medida FROM insumos WHERE id >= 156 ORDER BY id");
while ($x = $r->fetch_assoc()) {
    printf("insumo #%d [%s] %-28s tipo=%s producto_id=%s %s\n", $x['id'], $x['codigo'], $x['nombre_tecnico'],
        $x['tipo'], (string)$x['producto_id'], $x['unidad_medida']);
}

echo "\n=== 5. Esquema productos / tablas de precios ===\n";
$r = $m->query("SHOW COLUMNS FROM productos");
while ($x = $r->fetch_assoc()) { printf("  %-24s %-26s null=%s def=%s\n", $x['Field'], $x['Type'], $x['Null'], (string)$x['Default']); }
foreach (['%precio%', '%present%', '%lista%'] as $pat) {
    $r = $m->query("SHOW TABLES LIKE '$pat'");
    while ($x = $r->fetch_array()) { echo "  tabla: {$x[0]}\n"; }
}
echo "  --- ejemplo existentes (223, 222, 3, 404) ---\n";
$r = $m->query("SELECT id, codigo, unidad_venta, presentacion_principal, contenido_neto, unidad_contenido, precio_venta, rendimiento FROM productos WHERE id IN (223,222,3,404)");
while ($x = $r->fetch_assoc()) {
    printf("  #%d [%s] %s/%s %s%s precio=%s rend=%s\n", $x['id'], $x['codigo'], (string)$x['unidad_venta'],
        (string)$x['contenido_neto'], (string)$x['unidad_contenido'], (string)$x['presentacion_principal'],
        (string)$x['precio_venta'], (string)$x['rendimiento']);
}

echo "\n=== 6. ¿Existe presentaciones_producto? ===\n";
$r = $m->query("SHOW TABLES LIKE 'presentaciones_producto'");
echo ($r && $r->num_rows) ? "SÍ existe\n" : "NO existe\n";
$r = $m->query("SHOW TABLES LIKE '%presentacion%'");
while ($x = $r->fetch_array()) { echo "  tabla: {$x[0]}\n"; }

$r = $m->query("SELECT id, archivo, fecha, productos_importados, formulaciones_creadas, insumos_creados, estatus
                FROM log_importaciones ORDER BY id DESC LIMIT 3");
while ($x = $r->fetch_assoc()) {
    printf("#%d %s | %s | prod=%d form=%d ins=%d | %s\n", $x['id'], $x['archivo'], $x['fecha'],
        $x['productos_importados'], $x['formulaciones_creadas'], $x['insumos_creados'], $x['estatus']);
}

echo "\n=== 7. Enlaces insumo→semielaborado (A3) y evidencia #83 vs EC-1 ===\n";
$r = $m->query("SELECT id, codigo, nombre_tecnico, tipo, producto_id FROM insumos WHERE id IN (83,91,96,105,157) ORDER BY id");
while ($x = $r->fetch_assoc()) {
    printf("  insumo #%-4d [%s] %-30s tipo=%-10s producto_id=%s\n", $x['id'], $x['codigo'],
        $x['nombre_tecnico'], $x['tipo'], (string)$x['producto_id']);
}
echo "  --- candidatos PLIOWAY / RESINA / EC-1 en insumos ---\n";
$r = $m->query("SELECT id, codigo, nombre_tecnico, tipo, producto_id FROM insumos
                WHERE nombre_tecnico LIKE '%PLIOWAY%' OR nombre_tecnico LIKE '%EC-%' OR nombre_tecnico LIKE 'SOLUCION DE RESINA%'
                ORDER BY nombre_tecnico, id");
while ($x = $r->fetch_assoc()) {
    printf("  #%-4d [%s] %-34s tipo=%-10s producto_id=%s\n", $x['id'], $x['codigo'], $x['nombre_tecnico'],
        $x['tipo'], (string)$x['producto_id']);
}
echo "  --- formulaciones que usan insumo #83 ---\n";
$r = $m->query("SELECT f.id, f.producto_id, p.nombre, f.version, f.es_activa, d.porcentaje, d.cantidad
                FROM detalle_formulacion d
                JOIN formulaciones f ON f.id = d.formulacion_id
                JOIN productos p ON p.id = f.producto_id
                WHERE d.insumo_id = 83 ORDER BY f.producto_id, f.version");
while ($x = $r->fetch_assoc()) {
    printf("  form#%-4d prod#%-4d %-34s v%s activa=%s %s%% %s Kg\n", $x['id'], $x['producto_id'], $x['nombre'],
        $x['version'], $x['es_activa'], $x['porcentaje'], $x['cantidad']);
}
echo "  --- uso de EXXOL D-40 (#108) como componente ---\n";
$r = $m->query("SELECT DISTINCT f.producto_id, p.nombre, f.version, d.porcentaje, d.cantidad
                FROM detalle_formulacion d
                JOIN formulaciones f ON f.id = d.formulacion_id
                JOIN productos p ON p.id = f.producto_id
                WHERE d.insumo_id = 108 AND f.es_activa = 1 ORDER BY f.producto_id");
while ($x = $r->fetch_assoc()) {
    printf("  prod#%-4d %-34s v%s %s%% %s Kg\n", $x['producto_id'], $x['nombre'], $x['version'], $x['porcentaje'], $x['cantidad']);
}
echo "  --- componentes de las versiones nuevas (muestra 957, 961, 963) ---\n";
$r = $m->query("SELECT d.formulacion_id, d.insumo_id, i.nombre_tecnico, d.porcentaje, d.cantidad
                FROM detalle_formulacion d JOIN insumos i ON i.id = d.insumo_id
                WHERE d.formulacion_id IN (957,961,963) ORDER BY d.formulacion_id, d.orden");
$f = 0;
while ($x = $r->fetch_assoc()) {
    if ($x['formulacion_id'] !== $f) { $f = $x['formulacion_id']; echo "  -- form#$f\n"; }
    printf("     #%-4d %-34s %8s%% %10s Kg\n", $x['insumo_id'], $x['nombre_tecnico'], $x['porcentaje'], $x['cantidad']);
}
echo "  --- formulación activa del producto #214 (SOLUCION DE RESINA EC-1) ---\n";
$r = $m->query("SELECT f.id, f.version, d.insumo_id, i.nombre_tecnico, d.porcentaje, d.cantidad
                FROM formulaciones f
                JOIN detalle_formulacion d ON d.formulacion_id = f.id
                JOIN insumos i ON i.id = d.insumo_id
                WHERE f.producto_id = 214 AND f.es_activa = 1 ORDER BY d.orden");
while ($x = $r->fetch_assoc()) {
    printf("  form#%d v%s  insumo #%-4d %-34s %s%% %s Kg\n", $x['id'], $x['version'], $x['insumo_id'],
        $x['nombre_tecnico'], $x['porcentaje'], $x['cantidad']);
}
