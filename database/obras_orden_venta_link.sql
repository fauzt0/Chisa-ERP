-- Iteración 5: Vinculación Obra ↔ Orden de Venta
-- Ejecutar una sola vez

SET @dbname = DATABASE();

-- orden_venta_id en obras
SET @columnname = 'orden_venta_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'obras' AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE obras ADD COLUMN orden_venta_id INT(11) DEFAULT NULL COMMENT ''Orden de venta vinculada'' AFTER cliente_id'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Índice
SET @indexname = 'idx_obras_orden_venta';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'obras' AND INDEX_NAME = @indexname) > 0,
  'SELECT 1',
  'ALTER TABLE obras ADD INDEX idx_obras_orden_venta (orden_venta_id)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
