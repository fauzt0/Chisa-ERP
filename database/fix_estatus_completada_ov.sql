-- ============================================================================
-- Fix: 'Completada' faltaba en el ENUM de ordenes_venta.estatus
-- El UPDATE de produccion/Dashboard::actualizar_estatus_ajax escribía 'Completada'
-- y el ENUM (sin strict mode por conexión CI3) truncaba el valor a ''.
-- Repara además OV-2026-0009 (id 28) que quedó con estatus='' tras el fallo.
-- Idempotente. No tocar `obras` (su ENUM ya incluye 'Completada').
-- Fecha: 2026-09-17
-- ============================================================================

SET @dbname = DATABASE();
SET @tablename = 'ordenes_venta';
SET @columnname = 'estatus';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA=@dbname AND TABLE_NAME=@tablename AND COLUMN_NAME=@columnname
      AND COLUMN_TYPE LIKE '%Completada%') > 0,
  'SELECT 1',
  "ALTER TABLE ordenes_venta MODIFY COLUMN estatus ENUM('Cotización','Confirmada','En Preparación','Entregada','Cancelada','Completada') NULL DEFAULT 'Cotización'"
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Reparar OV-2026-0009 (id 28): quedó estatus='' por el truncado del ENUM
UPDATE ordenes_venta SET estatus='En Preparación' WHERE id=28 AND estatus='';
