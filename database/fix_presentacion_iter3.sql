-- ============================================================================
-- FIX presentación iteración 3 (idempotente)
-- Aplica columnas de final_alignment.sql SIN reescribir ENUMs que quiten 'Venta'.
-- Conserva 'Devolucion' sin acento (el código escribe ese valor).
-- ============================================================================

SET @dbname = DATABASE();

-- 1. LOTES: orden_produccion_id nullable + trazabilidad OV/obra
ALTER TABLE lotes_produccion MODIFY COLUMN orden_produccion_id INT NULL;

SET @tablename = 'lotes_produccion';

SET @columnname = 'orden_venta_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE lotes_produccion ADD COLUMN orden_venta_id INT NULL AFTER orden_produccion_id'
));
PREPARE alterStmt FROM @preparedStatement; EXECUTE alterStmt; DEALLOCATE PREPARE alterStmt;

SET @columnname = 'obra_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE lotes_produccion ADD COLUMN obra_id INT NULL AFTER orden_venta_id'
));
PREPARE alterStmt FROM @preparedStatement; EXECUTE alterStmt; DEALLOCATE PREPARE alterStmt;

SET @indexname = 'idx_ov';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'lotes_produccion' AND INDEX_NAME = @indexname) > 0,
  'SELECT 1',
  'ALTER TABLE lotes_produccion ADD INDEX idx_ov (orden_venta_id)'
));
PREPARE alterStmt FROM @preparedStatement; EXECUTE alterStmt; DEALLOCATE PREPARE alterStmt;

SET @indexname = 'idx_obra';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'lotes_produccion' AND INDEX_NAME = @indexname) > 0,
  'SELECT 1',
  'ALTER TABLE lotes_produccion ADD INDEX idx_obra (obra_id)'
));
PREPARE alterStmt FROM @preparedStatement; EXECUTE alterStmt; DEALLOCATE PREPARE alterStmt;

-- 2. Fecha de completado en origen
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

-- 3. ENUMs: ampliar conservando valores actuales (incluye Venta; Devolucion sin acento)
ALTER TABLE movimientos_productos
  MODIFY COLUMN tipo_movimiento ENUM('Entrada','Salida','Ajuste','Produccion','Venta','Devolucion','Merma') NOT NULL;

ALTER TABLE movimientos_inventario
  MODIFY COLUMN tipo_movimiento ENUM('Entrada','Salida','Ajuste','Produccion','Merma','Devolucion') NOT NULL;
