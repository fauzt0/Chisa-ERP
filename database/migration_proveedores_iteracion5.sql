-- ============================================================
-- Iteración 5: Módulo de Proveedores — Comprobantes y mejoras
-- Fecha: 2026-08-06
-- ============================================================

-- 1. Agregar campos de comprobante a pagos_ordenes_compra
ALTER TABLE pagos_ordenes_compra
    ADD COLUMN comprobante_nombre VARCHAR(255) NULL AFTER notas,
    ADD COLUMN comprobante_ruta VARCHAR(500) NULL AFTER comprobante_nombre;

-- 2. Índice para búsqueda rápida de pagos por orden
CREATE INDEX idx_pagos_orden ON pagos_ordenes_compra(orden_compra_id);

-- 3. Tabla para configuración de notificaciones del módulo
CREATE TABLE IF NOT EXISTS notificaciones_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL COMMENT 'email, whatsapp, sistema',
    modulo VARCHAR(100) NOT NULL COMMENT 'proveedores, compras',
    evento VARCHAR(100) NOT NULL COMMENT 'nueva_oc, pago_realizado, comprobante_enviado',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unq_tipo_evento (tipo, modulo, evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inicializar configuración de notificaciones
INSERT IGNORE INTO notificaciones_config (tipo, modulo, evento, activo) VALUES
    ('email', 'compras', 'nueva_oc', 1),
    ('email', 'compras', 'pago_realizado', 1),
    ('whatsapp', 'compras', 'nueva_oc', 1),
    ('whatsapp', 'compras', 'pago_realizado', 1);
