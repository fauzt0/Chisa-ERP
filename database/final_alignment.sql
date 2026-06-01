-- ============================================================================
-- SCRIPT DE ALINEACIÓN FINAL: MÓDULO DE PRODUCCIÓN
-- ============================================================================
-- Este script realiza ajustes estructurales para asegurar que el Dashboard
-- funcione correctamente al completar ventas y obras directamente.
-- ============================================================================

-- 1. AJUSTES EN TABLA DE LOTES
-- Hacer que orden_produccion_id sea opcional y agregar vínculos directos
ALTER TABLE lotes_produccion MODIFY COLUMN orden_produccion_id INT NULL;

-- Agregar columnas de trazabilidad directa si no existen
SET @dbname = DATABASE();
SET @tablename = 'lotes_produccion';

-- orden_venta_id
SET @columnname = 'orden_venta_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE lotes_produccion ADD COLUMN orden_venta_id INT NULL AFTER orden_produccion_id'
));
PREPARE alterStmt FROM @preparedStatement; EXECUTE alterStmt; DEALLOCATE PREPARE alterStmt;

-- obra_id
SET @columnname = 'obra_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE lotes_produccion ADD COLUMN obra_id INT NULL AFTER orden_venta_id'
));
PREPARE alterStmt FROM @preparedStatement; EXECUTE alterStmt; DEALLOCATE PREPARE alterStmt;

-- Agregar Índices y Llaves
ALTER TABLE lotes_produccion ADD INDEX idx_ov (orden_venta_id);
ALTER TABLE lotes_produccion ADD INDEX idx_obra (obra_id);

-- 2. AJUSTES EN TABLAS DE ORIGEN
-- Agregar fecha_completado para reporte de dashboard
SET @tablename = 'ordenes_venta';
SET @columnname = 'fecha_completado_produccion';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE ordenes_venta ADD COLUMN fecha_completado_produccion DATETIME NULL'
));
PREPARE alterStmt FROM @preparedStatement; EXECUTE alterStmt; DEALLOCATE PREPARE alterStmt;

SET @tablename = 'obras';
SET @columnname = 'fecha_completado_produccion';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE obras ADD COLUMN fecha_completado_produccion DATETIME NULL'
));
PREPARE alterStmt FROM @preparedStatement; EXECUTE alterStmt; DEALLOCATE PREPARE alterStmt;

-- 3. AJUSTES EN MOVIMIENTOS DE INVENTARIO (Nuevos Tipos)
ALTER TABLE movimientos_productos MODIFY COLUMN tipo_movimiento ENUM('Entrada', 'Salida', 'Ajuste', 'Merma', 'Devolución', 'Produccion') NOT NULL;
ALTER TABLE movimientos_inventario MODIFY COLUMN tipo_movimiento ENUM('Entrada', 'Salida', 'Ajuste', 'Merma', 'Devolución', 'Produccion') NOT NULL;

-- 4. PERMISOS ADICIONALES (Garantizar acceso total)
-- Esto es opcional pero ayuda si hay nuevos controladores
-- INSERT IGNORE INTO permisos (nombre, descripcion) VALUES ('produccion_lotes', 'Acceso al historial de lotes');

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
