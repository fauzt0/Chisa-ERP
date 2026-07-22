<?php
// /var/www/html/iclock/panel.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/zkt_comandos.php';

$archivo_cola = ARCHIVO_COLA;
$archivo_log  = ARCHIVO_LOG;

if (!file_exists($archivo_cola)) {
    file_put_contents($archivo_cola, '[]');
}

$mensaje = '';
$tipo_mensaje = 'ok';

function encolar_comandos_locales(array $comandos, $al_frente = false) {
    $cola = json_decode(file_get_contents(ARCHIVO_COLA), true);
    if (!is_array($cola)) {
        $cola = [];
    }
    $base_id = time();
    $items = [];
    foreach ($comandos as $i => $cmd) {
        $items[] = ['id' => $base_id + $i, 'cmd' => $cmd];
    }
    if ($al_frente) {
        $cola = array_merge($items, $cola);
    } else {
        $cola = array_merge($cola, $items);
    }
    file_put_contents(ARCHIVO_COLA, json_encode($cola, JSON_PRETTY_PRINT));
    return count($comandos);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['btn_limpiar_log'])) {
        if (file_exists($archivo_log)) {
            unlink($archivo_log);
            $mensaje = '🗑️ Log del reloj borrado exitosamente.';
        }
    } elseif (isset($_POST['btn_limpiar_cola']) && MODO_PRUEBA_LOCAL) {
        file_put_contents($archivo_cola, '[]');
        $mensaje = '🗑️ Cola de comandos vaciada.';
    } elseif (isset($_POST['btn_borrar_todos'])) {
        $pin_desde = max(2, (int) ($_POST['pin_desde'] ?? 2));
        $pin_hasta = min(9999, max($pin_desde, (int) ($_POST['pin_hasta'] ?? 500)));

        $comandos = [];
        for ($pin = $pin_desde; $pin <= $pin_hasta; $pin++) {
            if (!pin_borrable($pin)) {
                continue;
            }
            $comandos = array_merge($comandos, comandos_borrar_usuario($pin));
        }

        if (empty($comandos)) {
            $mensaje = '⚠️ Rango inválido. El PIN 1 (admin) nunca se incluye.';
            $tipo_mensaje = 'warn';
        } elseif (!MODO_PRUEBA_LOCAL) {
            $mensaje = '⚠️ Estás en modo ERP. Para borrado masivo pon MODO_PRUEBA_LOCAL = true en config.php, '
                . 'ejecuta esta acción, espera a que el reloj vacíe la cola (~' . count($comandos) . ' comandos) '
                . 'y vuelve a false.';
            $tipo_mensaje = 'warn';
        } else {
            $n = encolar_comandos_locales($comandos);
            $mensaje = "✅ Encolados $n borrados (PIN {$pin_desde}-{$pin_hasta}). "
                . 'PIN ' . PIN_ADMIN_RELOJ . ' (admin) preservado. El reloj los procesa ~1 cada 15 seg.';
            if ($n > 100) {
                $mins = (int) ceil($n * 15 / 60);
                $mensaje .= " Tiempo estimado: ~{$mins} min.";
            }
        }
    } else {
        $cmd = '';

        if (isset($_POST['btn_alta_usuario'])) {
            $cmd = cmd_alta_usuario($_POST['pin'], $_POST['nombre'], $_POST['pass'] ?? '');
        }

        if (isset($_POST['btn_consulta_general'])) {
            $inicio = $_POST['fecha_inicio'] . ' 00:00:00';
            $fin = $_POST['fecha_fin'] . ' 23:59:59';
            $cmd = "DATA QUERY ATTLOG StartTime=$inicio\tEndTime=$fin";
        }

        if (isset($_POST['btn_borrar_usuario'])) {
            $pin = (int) ($_POST['pin_borrar'] ?? 0);
            if (!pin_borrable($pin)) {
                $mensaje = '⛔ PIN inválido o protegido. No se puede borrar el PIN '
                    . PIN_ADMIN_RELOJ . ' (administrador). Usa PIN 2 o mayor.';
                $tipo_mensaje = 'warn';
            } elseif (MODO_PRUEBA_LOCAL) {
                $cmds = comandos_borrar_usuario($pin);
                encolar_comandos_locales($cmds, true);
                $mensaje = '✅ Encolados: ' . htmlspecialchars(implode(' | ', $cmds))
                    . ' — el reloj los procesa en ~15 seg c/u.';
            } else {
                $cmds = comandos_borrar_usuario($pin);
                $mensaje = 'ℹ️ Modo ERP: encola desde rh/RelojChecador/dispositivos. Comandos: '
                    . htmlspecialchars(implode(' | ', $cmds));
                $tipo_mensaje = 'warn';
            }
        } elseif (!empty($cmd)) {
            if (MODO_PRUEBA_LOCAL) {
                $prioritario = isset($_POST['btn_borrar_usuario']) || isset($_POST['btn_alta_usuario']);
                encolar_comandos_locales([$cmd], $prioritario);
                $mensaje = '✅ Encolado: ' . htmlspecialchars($cmd) . ' — el reloj lo toma en ~15 seg.';
            } else {
                $mensaje = 'ℹ️ Modo ERP: encola desde rh/RelojChecador/dispositivos. '
                    . 'Comando sugerido: ' . htmlspecialchars($cmd);
                $tipo_mensaje = 'warn';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - ZKTeco (Proxy)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; color: #333; padding: 20px; }
        h1 { color: #2c3e50; text-align: center; }
        .modo-badge { text-align: center; margin-bottom: 20px; font-weight: bold; }
        .modo-local { color: #d35400; background: #fdebd0; padding: 10px; border-radius: 5px; display: inline-block; }
        .modo-erp { color: #27ae60; background: #d5f5e3; padding: 10px; border-radius: 5px; display: inline-block; }
        .container { max-width: 800px; margin: 0 auto; display: grid; gap: 20px; grid-template-columns: 1fr; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #34495e; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        .card-danger { border: 2px solid #e74c3c; }
        input { width: 100%; padding: 10px; margin: 8px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        button { background-color: #3498db; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; }
        button:hover { background-color: #2980b9; }
        .btn-danger { background-color: #e74c3c; }
        .btn-danger:hover { background-color: #c0392b; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center; border: 1px solid; }
        .alert-ok { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-warn { background-color: #fff3cd; color: #856404; border-color: #ffeeba; }
        .hint { font-size: 13px; color: #666; margin: 0 0 12px; line-height: 1.4; }
        .queue-box { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; font-family: monospace; }
        .log-box { background: #1a252f; color: #00ff00; padding: 15px; border-radius: 5px; overflow-y: auto; max-height: 400px; font-family: monospace; white-space: pre-wrap; margin-top: 10px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>

    <h1>Panel de Herramientas ZKTeco</h1>

    <div class="modo-badge">
        <?php if (MODO_PRUEBA_LOCAL): ?>
            <span class="modo-local">⚠️ MODO LOCAL — comandos vía comandos.json</span>
        <?php else: ?>
            <span class="modo-erp">☁️ MODO ERP — asistencias a la nube; comandos desde RH web</span>
        <?php endif; ?>
    </div>

    <?php if ($mensaje != ''): ?>
        <div class="alert alert-<?php echo $tipo_mensaje === 'warn' ? 'warn' : 'ok'; ?>"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <div class="container">

        <div class="card">
            <h3>1. Alta de Empleado en Reloj</h3>
            <p class="hint">Formato ADMS con tabuladores (<code>\t</code>). Si el ERP guarda el comando mal (ej. <code>/tName</code> en vez de tab), el reloj crea usuarios corruptos.</p>
            <form method="POST">
                <input type="number" name="pin" placeholder="PIN (ID ERP, ej. 1001)" required min="2">
                <input type="text" name="nombre" placeholder="Nombre (ej. Juan Perez)" required>
                <input type="number" name="pass" placeholder="Contraseña numérica (opcional)">
                <button type="submit" name="btn_alta_usuario">Encolar Alta</button>
            </form>
        </div>

        <div class="card">
            <h3>2. Forzar Sincronización de Asistencias</h3>
            <form method="POST">
                <label>Fecha Inicio:</label>
                <input type="date" name="fecha_inicio" required>
                <label>Fecha Fin:</label>
                <input type="date" name="fecha_fin" required>
                <button type="submit" name="btn_consulta_general">Encolar Extracción</button>
            </form>
        </div>

        <div class="card">
            <h3>3. Borrar un Usuario (por PIN)</h3>
            <form method="POST">
                <input type="number" name="pin_borrar" placeholder="PIN a borrar (ej. 2)" required min="2" step="1">
                <button type="submit" name="btn_borrar_usuario" class="btn-danger">Encolar Borrado</button>
            </form>
            <p class="hint">Secuencia MB10-VL: <code>DATA DELETE FACE PIN={pin}</code> y luego <code>DATA DELETE USER PIN={pin}</code>. PIN <?php echo PIN_ADMIN_RELOJ; ?> (admin) nunca se borra.</p>
        </div>

        <div class="card card-danger">
            <h3>4. Borrar TODOS los usuarios (excepto admin PIN <?php echo PIN_ADMIN_RELOJ; ?>)</h3>
            <p class="hint">
                Encola un <code>DATA DELETE USER PIN=n</code> por cada número del rango.
                Útil para limpiar usuarios corruptos. Requiere <strong>MODO_PRUEBA_LOCAL = true</strong>.
            </p>
            <form method="POST" onsubmit="return confirm('¿Borrar todos los PIN del rango indicado? El PIN <?php echo PIN_ADMIN_RELOJ; ?> (admin) NO se toca.');">
                <div class="row-2">
                    <div>
                        <label>PIN desde:</label>
                        <input type="number" name="pin_desde" value="2" min="2" max="9999" required>
                    </div>
                    <div>
                        <label>PIN hasta:</label>
                        <input type="number" name="pin_hasta" value="500" min="2" max="9999" required>
                    </div>
                </div>
                <button type="submit" name="btn_borrar_todos" class="btn-danger">🗑️ Encolar borrado masivo</button>
            </form>
        </div>

        <div class="card">
            <h3>Órdenes Pendientes</h3>
            <?php if (MODO_PRUEBA_LOCAL): ?>
            <form method="POST" style="margin-bottom: 8px;">
                <button type="submit" name="btn_limpiar_cola" class="btn-danger" style="width: auto; padding: 8px 15px; font-size: 14px;" onclick="return confirm('¿Vaciar toda la cola de comandos?');">🗑️ Vaciar cola</button>
            </form>
            <?php endif; ?>
            <div class="queue-box">
                <?php
                if (MODO_PRUEBA_LOCAL && file_exists($archivo_cola)) {
                    $cola_actual = json_decode(file_get_contents($archivo_cola), true);
                    if (is_array($cola_actual) && count($cola_actual) > 0) {
                        echo '<pre style="margin:0;">' . htmlspecialchars(print_r($cola_actual, true)) . '</pre>';
                        echo "\nTotal en cola: " . count($cola_actual);
                    } else {
                        echo 'No hay órdenes pendientes. Todo limpio.';
                    }
                } elseif (!MODO_PRUEBA_LOCAL) {
                    echo "Modo ERP: cola en rh/RelojChecador/dispositivos.\n";
                    echo 'Serial: ' . RELOJ_SN;
                } else {
                    echo 'comandos.json vacío.';
                }
                ?>
            </div>
        </div>

        <div class="card">
            <h3>Monitor del Reloj (datos_reloj.txt)</h3>
            <form method="POST" style="margin-bottom: 5px;">
                <button type="submit" name="btn_limpiar_log" class="btn-danger" style="width: auto; padding: 8px 15px; font-size: 14px;">🗑️ Limpiar Log</button>
            </form>
            <div class="log-box">
                <?php
                if (file_exists($archivo_log)) {
                    $contenido = file_get_contents($archivo_log);
                    echo !empty($contenido) ? htmlspecialchars($contenido) : 'El log está vacío. Esperando datos...';
                } else {
                    echo 'Aún no se ha creado el archivo de log.';
                }
                ?>
            </div>
        </div>

    </div>
</body>
</html>
