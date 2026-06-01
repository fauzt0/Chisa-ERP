-- ================================================================
-- MIGRACIÓN: Agregar campos de trazabilidad de origen a ordenes_compra
-- Permite rastrear qué pre-órdenes fueron generadas desde módulo de Producción
-- 
-- EJECUTAR EN: Base de datos de producción CHISA ERP
-- AUTOR: Antigravity — Módulo de Producción
-- FECHA: 2026-02-23
-- ================================================================

-- 1. Agregar columnas de origen (si no existen)
ALTER TABLE `ordenes_compra`
    ADD COLUMN IF NOT EXISTS `origen` VARCHAR(50) NULL DEFAULT NULL 
        COMMENT 'Módulo que originó la OC (ej: Produccion, Compras)',
    ADD COLUMN IF NOT EXISTS `origen_tipo` VARCHAR(30) NULL DEFAULT NULL 
        COMMENT 'Tipo de registro de origen (ej: venta, obra, interno)';

-- 2. Índice para consultas por origen
CREATE INDEX IF NOT EXISTS `idx_oc_origen` 
    ON `ordenes_compra` (`origen`, `origen_tipo`);

-- ================================================================
-- VERIFICACIÓN: Confirmar que las columnas fueron creadas
-- ================================================================
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT, 
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'ordenes_compra'
  AND COLUMN_NAME IN ('origen', 'origen_tipo');
