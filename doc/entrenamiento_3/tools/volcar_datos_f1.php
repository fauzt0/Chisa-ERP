<?php
/**
 * FASE 1 — Volcado de solo lectura para el matching del entrenamiento 3.
 *
 * Uso:  php doc/entrenamiento_3/tools/volcar_datos_f1.php [carpeta_salida]
 *
 * Genera 4 archivos JSON con el estado actual del catálogo de producción
 * (productos, insumos, formulaciones y detalle). NO escribe en la base de datos.
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');

$db = [];
require dirname(__DIR__, 3) . '/application/config/database.php';
$cfg = $db['default'];

$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($m->connect_error) {
    fwrite(STDERR, "Conexion fallida: {$m->connect_error}\n");
    exit(1);
}
$m->set_charset('utf8mb4');

$outdir = $argv[1] ?? sys_get_temp_dir() . '/f1_dump';
if (!is_dir($outdir) && !mkdir($outdir, 0770, true)) {
    fwrite(STDERR, "No se pudo crear $outdir\n");
    exit(1);
}

$tablas = [
    'productos' => "SELECT id, codigo, nombre, alias, tipo_producto, categoria_id, unidad_venta,
                           presentacion_principal, contenido_neto, unidad_contenido, rendimiento,
                           precio_venta, estatus
                    FROM productos ORDER BY nombre",
    'insumos' => "SELECT id, codigo, nombre_tecnico, alias, tipo, producto_id, unidad_medida,
                         precio_promedio, stock_actual, estatus
                  FROM insumos ORDER BY nombre_tecnico",
    'formulaciones' => "SELECT id, producto_id, version, nombre_version, es_activa, cantidad_producida,
                               unidad_produccion, referencia_cliente, cliente_id, rendimiento_m2_por_kg,
                               comentarios, fecha_creacion
                        FROM formulaciones ORDER BY producto_id, version",
    'detalle_formulacion' => "SELECT id, formulacion_id, tipo_componente, insumo_id, producto_id, cantidad,
                                     unidad, porcentaje, grupo_color, porcentaje_fase_acuosa, kg_fase_acuosa, orden
                              FROM detalle_formulacion ORDER BY formulacion_id, orden, id",
];

foreach ($tablas as $nombre => $sql) {
    $res = $m->query($sql);
    if (!$res) {
        // Columna inexistente en esta instalación: reintenta sin ella para no abortar todo el volcado
        fwrite(STDERR, "AVISO: fallo la consulta de $nombre ({$m->error}); se omite.\n");
        continue;
    }
    $filas = [];
    while ($fila = $res->fetch_assoc()) {
        $filas[] = $fila;
    }
    $ruta = "$outdir/$nombre.json";
    file_put_contents($ruta, json_encode($filas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    printf("%-22s %6d filas -> %s\n", $nombre, count($filas), $ruta);
}

$m->close();
