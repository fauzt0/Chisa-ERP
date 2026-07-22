<?php
// config.php — configuración central del proxy ZKTeco

// true  = comandos y asistencias en local (comandos.json + datos_reloj.txt)
// false = asistencias y comandos vía API /api/reloj/* del ERP
define('MODO_PRUEBA_LOCAL', false);

// Serial del reloj — debe coincidir con reloj_dispositivos en el ERP y con el API_TOKEN asignado
define('RELOJ_SN', 'UDP3252700203');

// Solo pruebas: true envía a sync_asistencias_debug (sin token, no guarda en BD)
// En producción debe ser false → sync_asistencias
define('MODO_SYNC_DEBUG', false);

define('ARCHIVO_COLA', __DIR__ . '/comandos.json');
define('ARCHIVO_LOG', __DIR__ . '/datos_reloj.txt');
define('ARCHIVO_LOG_CMD', __DIR__ . '/log_comandos.txt');

// PIN del administrador del reloj — nunca se borra en limpieza masiva
define('PIN_ADMIN_RELOJ', 1);

function escribir_log($archivo, $contenido, $append = true) {
    $flags = $append ? FILE_APPEND | LOCK_EX : LOCK_EX;
    $ok = @file_put_contents($archivo, $contenido, $flags);
    if ($ok === false) {
        error_log("iclock: no se pudo escribir en $archivo — revisa permisos (usuario apache)");
    }
    return $ok !== false;
}
