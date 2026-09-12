<?php
/**
 * TEMPORAL — buscador de productos / formulaciones para el entrenamiento 3
 * Uso:
 *   php doc/_tmp_db2.php find "TEXTO"                 -> productos que coincidan (nombre/alias/codigo)
 *   php doc/_tmp_db2.php form <producto_id>           -> formulaciones del producto + componentes
 *   php doc/_tmp_db2.php activas <producto_id>        -> solo la formulación activa con sus componentes
 *   php doc/_tmp_db2.php insumos "TEXTO"              -> insumos/materias primas que coincidan
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
$db = [];
require dirname(__DIR__, 3) . '/application/config/database.php';
$cfg = $db['default'];

$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($m->connect_error) { die("Conexion fallida\n"); }
$m->set_charset('utf8');

$cmd = $argv[1] ?? '';

function esc($s) { global $m; return $m->real_escape_string($s); }

switch ($cmd) {
    case 'find':
        $q = esc($argv[2] ?? '');
        $r = $m->query("SELECT id, codigo, nombre, alias, tipo_producto, unidad_venta, presentacion_principal, contenido_neto, rendimiento, estatus
                        FROM productos
                        WHERE nombre LIKE '%$q%' OR alias LIKE '%$q%' OR codigo LIKE '%$q%'
                        ORDER BY nombre LIMIT 40");
        echo "-- productos que coinciden con '$q' --\n";
        $n = 0;
        while ($row = $r->fetch_assoc()) {
            $n++;
            printf("#%d [%s] %s | alias=%s | %s %s/%s | rend=%s | %s\n",
                $row['id'], $row['codigo'], $row['nombre'], (string)$row['alias'], $row['tipo_producto'],
                (string)$row['unidad_venta'], (string)$row['contenido_neto'], (string)$row['rendimiento'], $row['estatus']);
        }
        if (!$n) echo "(sin coincidencias)\n";
        break;

    case 'insumos':
        $q = esc($argv[2] ?? '');
        $r = $m->query("SELECT id, codigo, nombre_tecnico, tipo, unidad_medida, precio_promedio, estatus
                        FROM insumos
                        WHERE nombre_tecnico LIKE '%$q%' OR alias LIKE '%$q%' OR codigo LIKE '%$q%'
                        ORDER BY nombre_tecnico LIMIT 40");
        if (!$r) { echo "ERROR: " . $m->error . "\n"; break; }
        echo "-- insumos que coinciden con '$q' --\n";
        $n = 0;
        while ($row = $r->fetch_assoc()) {
            $n++;
            printf("#%d [%s] %s | tipo=%s | %s | costo_promedio=%s | %s\n",
                $row['id'], $row['codigo'], $row['nombre_tecnico'], $row['tipo'],
                (string)$row['unidad_medida'], (string)$row['precio_promedio'], $row['estatus']);
        }
        if (!$n) echo "(sin coincidencias)\n";
        break;

    case 'form':
    case 'activas':
        $pid = (int)($argv[2] ?? 0);
        $p = $m->query("SELECT id, codigo, nombre, tipo_producto, unidad_venta, contenido_neto, rendimiento, estatus FROM productos WHERE id = $pid")->fetch_assoc();
        if (!$p) { echo "producto $pid no existe\n"; break; }
        printf("PRODUCTO #%d [%s] %s | %s | %s %s | rend=%s | %s\n",
            $p['id'], $p['codigo'], $p['nombre'], $p['tipo_producto'], (string)$p['unidad_venta'], (string)$p['contenido_neto'], (string)$p['rendimiento'], $p['estatus']);
        $where = ($cmd === 'activas') ? "AND f.es_activa = 1" : "";
        $f = $m->query("SELECT f.id, f.version, f.nombre_version, f.es_activa, f.cantidad_producida, f.unidad_produccion,
                               f.rendimiento_m2_por_kg, f.cantidad_cubetas_ref, f.referencia_cliente, f.cliente_id,
                               f.costo_total_insumos, f.costo_total, f.fecha_creacion
                        FROM formulaciones f WHERE f.producto_id = $pid $where ORDER BY f.version DESC, f.id DESC LIMIT 12");
        while ($row = $f->fetch_assoc()) {
            printf("\n  FORM #%d v%s (activa=%s) lote=%s %s | m2/kg=%s | cubetas=%s | ref_cliente=%s | cliente_id=%s | costo_insumos=%s | costo_total=%s | %s\n",
                $row['id'], $row['version'], $row['es_activa'], (string)$row['cantidad_producida'], (string)$row['unidad_produccion'],
                (string)$row['rendimiento_m2_por_kg'], (string)$row['cantidad_cubetas_ref'], (string)$row['referencia_cliente'],
                (string)$row['cliente_id'], (string)$row['costo_total_insumos'], (string)$row['costo_total'], $row['fecha_creacion']);
            if (!empty($row['nombre_version'])) echo "    nombre_version: {$row['nombre_version']}\n";
            $d = $m->query("SELECT df.id, df.tipo_componente, df.insumo_id, df.producto_id, df.cantidad, df.unidad, df.porcentaje,
                                   df.costo_unitario, df.grupo_color, df.orden,
                                   COALESCE(i.nombre_tecnico, p2.nombre) AS componente
                            FROM detalle_formulacion df
                            LEFT JOIN insumos i ON i.id = df.insumo_id
                            LEFT JOIN productos p2 ON p2.id = df.producto_id
                            WHERE df.formulacion_id = {$row['id']} ORDER BY df.orden, df.id");
            while ($c = $d->fetch_assoc()) {
                printf("    [%s] %-38s %8s %-5s %7s%% | costo_u=%s | color=%s\n",
                    $c['tipo_componente'], (string)$c['componente'], (string)$c['cantidad'], (string)$c['unidad'],
                    (string)$c['porcentaje'], (string)$c['costo_unitario'], (string)$c['grupo_color']);
            }
        }
        break;

    case 'resumen':
        // inventario global compacto
        $r = $m->query("SELECT COUNT(*) c FROM productos"); $x = $r->fetch_assoc();
        echo "productos: {$x['c']}\n";
        $r = $m->query("SELECT COUNT(*) c FROM formulaciones"); $x = $r->fetch_assoc();
        echo "formulaciones: {$x['c']}\n";
        $r = $m->query("SELECT SUM(es_activa=1) c FROM formulaciones"); $x = $r->fetch_assoc();
        echo "formulaciones activas: {$x['c']}\n";
        $r = $m->query("SELECT COUNT(*) c FROM insumos"); $x = $r->fetch_assoc();
        echo "insumos: {$x['c']}\n";
        $r = $m->query("SELECT MIN(fecha_creacion) a, MAX(fecha_creacion) b FROM formulaciones"); $x = $r->fetch_assoc();
        echo "formulaciones fechas: {$x['a']} .. {$x['b']}\n";
        break;

    default:
        echo "comandos: find | insumos | form | activas | resumen\n";
}
