<?php
/**
 * Script de migración para Proveedores - Iteración 5
 * Ejecutar: php database/migrar_proveedores.php
 * O vía CI: php index.php compras/Migracion aplicar
 */
define('BASEPATH', true);

// Cargar configuración
$app_path = __DIR__ . '/../application/';
require $app_path . 'config/database.php';

$cfg = $db['default'];
$mysqli = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error . "\n");
}

echo "Conectado a: {$cfg['database']}\n\n";

// 1. Agregar campos de comprobante
echo "1. Agregando comprobante_nombre... ";
$mysqli->query("ALTER TABLE pagos_ordenes_compra ADD COLUMN comprobante_nombre VARCHAR(255) NULL AFTER notas");
echo ($mysqli->error ? "SKIP: " . $mysqli->error : "OK") . "\n";

echo "2. Agregando comprobante_ruta... ";
$mysqli->query("ALTER TABLE pagos_ordenes_compra ADD COLUMN comprobante_ruta VARCHAR(500) NULL AFTER comprobante_nombre");
echo ($mysqli->error ? "SKIP: " . $mysqli->error : "OK") . "\n";

// 3. Índice
echo "3. Creando índice pagos_orden... ";
$mysqli->query("CREATE INDEX idx_pagos_orden ON pagos_ordenes_compra(orden_compra_id)");
echo ($mysqli->error ? "SKIP: " . $mysqli->error : "OK") . "\n";

// 4. Tabla notificaciones
echo "4. Creando tabla notificaciones_config... ";
$mysqli->query("CREATE TABLE IF NOT EXISTS notificaciones_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL COMMENT 'email, whatsapp, sistema',
    modulo VARCHAR(100) NOT NULL COMMENT 'proveedores, compras',
    evento VARCHAR(100) NOT NULL COMMENT 'nueva_oc, pago_realizado',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unq_tipo_evento (tipo, modulo, evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo ($mysqli->error ? "SKIP: " . $mysqli->error : "OK") . "\n";

echo "\n✅ Migración completada.\n";
$mysqli->close();
